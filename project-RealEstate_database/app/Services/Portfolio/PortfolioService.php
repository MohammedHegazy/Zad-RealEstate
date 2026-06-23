<?php

namespace App\Services\Portfolio;

use App\Exceptions\Portfolio\DuplicatePortfolioItemException;
use App\Exceptions\Portfolio\EstateAlreadyTakenException;
use App\Exceptions\Portfolio\EstateNotPublishedException;
use App\Exceptions\Portfolio\InvalidPortfolioStatusTransitionException;
use App\Exceptions\Portfolio\PortfolioItemNotFoundException;
use App\Exceptions\Portfolio\UnauthorizedPortfolioAccessException;
use App\Models\Estate;
use App\Models\InvestmentPortfolio;
use App\Models\PortfolioProperty;
use App\Models\User;
use App\Services\Investment\InvestorDashboardService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Portfolio domain service — all investor portfolio business rules live here.
 *
 * Controllers authorize via policies, validate HTTP input via Form Requests,
 * then delegate to this service.
 */
class PortfolioService
{
    /** Allowed status transitions (terminal states have no outgoing edges). */
    private const STATUS_TRANSITIONS = [
        PortfolioProperty::STATUS_TRACKING => [PortfolioProperty::STATUS_INVESTED],
        PortfolioProperty::STATUS_INVESTED => [PortfolioProperty::STATUS_SOLD],
        PortfolioProperty::STATUS_SOLD => [],
    ];

    public function __construct(
        private readonly InvestorDashboardService $dashboard,
    ) {}

    /**
     * Ensure the user has exactly one default portfolio (create if missing).
     *
     * Uses a transaction + row lock on the user to avoid duplicate defaults under concurrency.
     */
    public function ensureDefaultPortfolio(User $user): InvestmentPortfolio
    {
        return DB::transaction(function () use ($user) {
            User::query()->whereKey($user->id)->lockForUpdate()->first();

            $default = InvestmentPortfolio::query()
                ->forUser($user->id)
                ->default()
                ->first();

            if ($default !== null) {
                return $default;
            }

            return InvestmentPortfolio::query()->create([
                'user_id' => $user->id,
                'name' => config('realestate.default_portfolio_name', 'My Portfolio'),
                'description' => null,
                'target_budget' => null,
                'risk_level' => 'moderate',
                'status' => InvestmentPortfolio::STATUS_ACTIVE,
                'is_default' => true,
            ]);
        });
    }

    /**
     * Add an active estate to the user's default portfolio (or a specific portfolio_id).
     *
     * @param  array{
     *     portfolio_id?: int,
     *     status?: string,
     *     investment_amount?: float|int|string|null,
     *     notes?: string|null
     * }  $data
     */
    public function addEstateToPortfolio(User $user, Estate $estate, array $data = []): PortfolioProperty
    {
        $this->assertEstateIsPublished($estate);

        if ($this->isEstateGloballyTaken($estate)) {
            throw new EstateAlreadyTakenException($estate->id);
        }

        return DB::transaction(function () use ($user, $estate, $data) {
            $portfolio = isset($data['portfolio_id'])
                ? $this->resolvePortfolioForUser($user, (int) $data['portfolio_id'])
                : $this->ensureDefaultPortfolio($user);

            if ($this->portfolioContainsEstate($portfolio, $estate->id)) {
                throw new DuplicatePortfolioItemException($portfolio->id, $estate->id);
            }

            $status = $this->normalizeStatus(
                (string) Arr::get($data, 'status', PortfolioProperty::STATUS_TRACKING)
            );

            $attributes = $this->buildItemAttributes($status, $data);

            /** @var PortfolioProperty $item */
            $item = $portfolio->properties()->create([
                'estate_id' => $estate->id,
                ...$attributes,
            ]);

            return $item->load('estate');
        });
    }

    /**
     * Update portfolio item status (tracking → invested → sold only).
     *
     * @param  array{
     *     investment_amount?: float|int|string|null,
     *     notes?: string|null
     * }  $data
     */
    public function updatePortfolioItemStatus(
        User $user,
        PortfolioProperty $item,
        string $newStatus,
        array $data = [],
    ): PortfolioProperty {
        $this->assertUserOwnsItem($user, $item);

        $newStatus = $this->normalizeStatus($newStatus);

        if ($item->status === $newStatus) {
            return $this->applyOptionalItemFields($item, $data);
        }

        if (! $this->canTransition($item->status, $newStatus)) {
            throw new InvalidPortfolioStatusTransitionException($item->status, $newStatus);
        }

        $this->assertEstateNotTakenByOthers($user, $item->estate_id, $newStatus);

        return DB::transaction(function () use ($item, $newStatus, $data) {
            $item->status = $newStatus;
            $this->applyStatusTimestamps($item, $newStatus);
            $this->mergeItemFields($item, $data);

            $item->save();

            return $item->fresh(['estate']);
        });
    }

    /**
     * Remove an estate from the user's portfolio(s) by estate id.
     */
    public function removeEstateFromPortfolio(User $user, Estate $estate): void
    {
        $deleted = PortfolioProperty::query()
            ->where('estate_id', $estate->id)
            ->whereHas('portfolio', fn (Builder $q) => $q->where('user_id', $user->id))
            ->delete();

        if ($deleted === 0) {
            throw new PortfolioItemNotFoundException($user->id, $estate->id);
        }
    }

    /**
     * Remove a specific portfolio item (after policy check in controller).
     */
    public function removePortfolioItem(User $user, PortfolioProperty $item): void
    {
        $this->assertUserOwnsItem($user, $item);
        $item->delete();
    }

    /**
     * @param  array{
     *     name: string,
     *     description?: string|null,
     *     target_budget?: float|int|string|null,
     *     risk_level?: string,
     *     status?: string,
     *     is_default?: bool
     * }  $data
     */
    public function createPortfolio(User $user, array $data): InvestmentPortfolio
    {
        return DB::transaction(function () use ($user, $data) {
            if (! empty($data['is_default'])) {
                InvestmentPortfolio::query()
                    ->forUser($user->id)
                    ->update(['is_default' => false]);
            }

            return InvestmentPortfolio::query()->create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'target_budget' => $data['target_budget'] ?? null,
                'risk_level' => $data['risk_level'] ?? 'moderate',
                'status' => $data['status'] ?? InvestmentPortfolio::STATUS_ACTIVE,
                'is_default' => (bool) ($data['is_default'] ?? false),
            ]);
        });
    }

    public function listPortfolios(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return InvestmentPortfolio::query()
            ->forUser($user->id)
            ->withCount('properties')
            ->latest()
            ->paginate($perPage);
    }

    public function showPortfolio(User $user, InvestmentPortfolio $portfolio): InvestmentPortfolio
    {
        $this->assertUserOwnsPortfolio($user, $portfolio);

        return $portfolio->loadCount('properties');
    }

    /**
     * @param  array{portfolio_id?: int, status?: string}  $filters
     */
    public function listPortfolioItems(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PortfolioProperty::query()
            ->whereHas('portfolio', fn (Builder $q) => $q->where('user_id', $user->id))
            ->with(['portfolio', 'estate.place']);

        if (! empty($filters['portfolio_id'])) {
            $this->resolvePortfolioForUser($user, (int) $filters['portfolio_id']);
            $query->where('portfolio_id', (int) $filters['portfolio_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $this->normalizeStatus((string) $filters['status']));
        }

        $items = $query->latest()->paginate($perPage);

        $this->applyGlobalTakenFlag($items->items());

        return $items;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePortfolioItem(User $user, PortfolioProperty $item, array $data): PortfolioProperty
    {
        if (isset($data['status'])) {
            return $this->updatePortfolioItemStatus(
                $user,
                $item,
                (string) $data['status'],
                $data,
            );
        }

        $this->assertUserOwnsItem($user, $item);

        return $this->applyOptionalItemFields($item, $data);
    }

    public function listPortfolioProperties(User $user, InvestmentPortfolio $portfolio): \Illuminate\Support\Collection
    {
        $this->assertUserOwnsPortfolio($user, $portfolio);

        $items = $portfolio->properties()
            ->with('estate.place')
            ->latest()
            ->get();

        $this->applyGlobalTakenFlag($items->all());

        return $items;
    }

    public function calculateTotalInvestedAmount(User $user, ?InvestmentPortfolio $portfolio = null): float
    {
        $query = PortfolioProperty::query()
            ->where('status', PortfolioProperty::STATUS_INVESTED)
            ->whereHas('portfolio', fn (Builder $q) => $q->where('user_id', $user->id));

        if ($portfolio !== null) {
            $this->assertUserOwnsPortfolio($user, $portfolio);
            $query->where('portfolio_id', $portfolio->id);
        }

        return round((float) $query->sum('investment_amount'), 2);
    }

    /**
     * @return array<string, mixed>
     */
    public function getDashboardSummary(User $user): array
    {
        return $this->dashboard->getSummary($user);
    }

    /**
     * Aggregated metrics for the investor dashboard (single query, no N+1).
     *
     * @return array{
     *     portfolio_id: int|null,
     *     portfolio_name: string|null,
     *     total_invested: float,
     *     weighted_average_roi: float|null,
     *     expected_annual_income: float,
     *     counts_by_status: array<string, int>,
     *     total_items: int
     * }
     */
    public function getPortfolioSummary(User $user, ?InvestmentPortfolio $portfolio = null): array
    {
        $portfolio ??= $this->ensureDefaultPortfolio($user);

        if ($portfolio->user_id !== $user->id) {
            throw new UnauthorizedPortfolioAccessException();
        }

        $statuses = PortfolioProperty::statuses();
        $countsByStatus = array_fill_keys($statuses, 0);

        $rows = PortfolioProperty::query()
            ->where('portfolio_id', $portfolio->id)
            ->join('estates', 'estates.id', '=', 'portfolio_properties.estate_id')
            ->select([
                'portfolio_properties.status',
                'portfolio_properties.investment_amount',
                'estates.roi',
                'estates.expected_annual_income',
            ])
            ->get();

        $totalInvested = 0.0;
        $expectedAnnualIncome = 0.0;
        $weightedRoiNumerator = 0.0;
        $weightedRoiDenominator = 0.0;

        foreach ($rows as $row) {
            $countsByStatus[$row->status] = ($countsByStatus[$row->status] ?? 0) + 1;

            if ($row->status !== PortfolioProperty::STATUS_INVESTED) {
                continue;
            }

            $amount = $row->investment_amount !== null ? (float) $row->investment_amount : 0.0;
            $totalInvested += $amount;

            if ($row->expected_annual_income !== null) {
                $expectedAnnualIncome += (float) $row->expected_annual_income;
            }

            if ($row->roi !== null && $amount > 0) {
                $weightedRoiNumerator += (float) $row->roi * $amount;
                $weightedRoiDenominator += $amount;
            }
        }

        $totalItems = array_sum($countsByStatus);

        return [
            'portfolio_id' => $portfolio->id,
            'portfolio_name' => $portfolio->name,
            'total_invested' => round($totalInvested, 2),
            'weighted_average_roi' => $weightedRoiDenominator > 0
                ? round($weightedRoiNumerator / $weightedRoiDenominator, 4)
                : null,
            'expected_annual_income' => round($expectedAnnualIncome, 2),
            'counts_by_status' => $countsByStatus,
            'total_items' => $totalItems,
        ];
    }

    private function isEstateGloballyTaken(Estate $estate): bool
    {
        return PortfolioProperty::query()
            ->where('estate_id', $estate->id)
            ->whereIn('status', [PortfolioProperty::STATUS_INVESTED, PortfolioProperty::STATUS_SOLD])
            ->exists();
    }

    private function assertEstateNotTakenByOthers(User $user, int $estateId, string $newStatus): void
    {
        if (! in_array($newStatus, [PortfolioProperty::STATUS_INVESTED, PortfolioProperty::STATUS_SOLD], true)) {
            return;
        }

        $taken = PortfolioProperty::query()
            ->where('estate_id', $estateId)
            ->whereIn('status', [PortfolioProperty::STATUS_INVESTED, PortfolioProperty::STATUS_SOLD])
            ->whereHas('portfolio', fn (Builder $q) => $q->where('user_id', '!=', $user->id))
            ->exists();

        if ($taken) {
            throw new EstateAlreadyTakenException($estateId);
        }
    }

    /**
     * @param  list<PortfolioProperty>  $items
     */
    private function applyGlobalTakenFlag(array $items): void
    {
        if ($items === []) {
            return;
        }

        $estateIds = array_map(fn (PortfolioProperty $i) => $i->estate_id, $items);

        $takenEstateIds = PortfolioProperty::query()
            ->whereIn('estate_id', $estateIds)
            ->whereIn('status', [PortfolioProperty::STATUS_INVESTED, PortfolioProperty::STATUS_SOLD])
            ->pluck('estate_id')
            ->unique()
            ->values()
            ->all();

        foreach ($items as $item) {
            $item->global_taken = in_array($item->estate_id, $takenEstateIds, true);
        }
    }

    private function resolvePortfolioForUser(User $user, int $portfolioId): InvestmentPortfolio
    {
        $portfolio = InvestmentPortfolio::query()
            ->whereKey($portfolioId)
            ->where('user_id', $user->id)
            ->first();

        if ($portfolio === null) {
            throw new UnauthorizedPortfolioAccessException('Portfolio not found or access denied.');
        }

        return $portfolio;
    }

    private function portfolioContainsEstate(InvestmentPortfolio $portfolio, int $estateId): bool
    {
        return $portfolio->properties()
            ->where('estate_id', $estateId)
            ->exists();
    }

    private function assertUserOwnsPortfolio(User $user, InvestmentPortfolio $portfolio): void
    {
        if ($portfolio->user_id !== $user->id) {
            throw new UnauthorizedPortfolioAccessException();
        }
    }

    private function assertEstateIsPublished(Estate $estate): void
    {
        if ($estate->status !== 'active') {
            throw new EstateNotPublishedException($estate->id, (string) $estate->status);
        }
    }

    private function assertUserOwnsItem(User $user, PortfolioProperty $item): void
    {
        $item->loadMissing('portfolio');

        if ($item->portfolio?->user_id !== $user->id) {
            throw new UnauthorizedPortfolioAccessException();
        }
    }

    private function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));

        if (! in_array($status, PortfolioProperty::statuses(), true)) {
            throw new InvalidArgumentException("Invalid portfolio item status [{$status}].");
        }

        return $status;
    }

    private function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::STATUS_TRANSITIONS[$from] ?? [], true);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function buildItemAttributes(string $status, array $data): array
    {
        $attributes = [
            'status' => $status,
            'investment_amount' => Arr::get($data, 'investment_amount'),
            'notes' => Arr::get($data, 'notes'),
            'invested_at' => null,
            'sold_at' => null,
        ];

        if ($status === PortfolioProperty::STATUS_INVESTED) {
            $attributes['invested_at'] = Carbon::now();
        }

        if ($status === PortfolioProperty::STATUS_SOLD) {
            $attributes['invested_at'] = Carbon::now();
            $attributes['sold_at'] = Carbon::now();
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function mergeItemFields(PortfolioProperty $item, array $data): void
    {
        if (array_key_exists('investment_amount', $data)) {
            $item->investment_amount = $data['investment_amount'];
        }

        if (array_key_exists('notes', $data)) {
            $item->notes = $data['notes'];
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyOptionalItemFields(PortfolioProperty $item, array $data): PortfolioProperty
    {
        if ($data === []) {
            return $item->loadMissing('estate');
        }

        $this->mergeItemFields($item, $data);
        $item->save();

        return $item->fresh(['estate']);
    }

    private function applyStatusTimestamps(PortfolioProperty $item, string $newStatus): void
    {
        if ($newStatus === PortfolioProperty::STATUS_INVESTED && $item->invested_at === null) {
            $item->invested_at = Carbon::now();
        }

        if ($newStatus === PortfolioProperty::STATUS_SOLD) {
            $item->sold_at = Carbon::now();

            if ($item->invested_at === null) {
                $item->invested_at = Carbon::now();
            }
        }
    }
}
