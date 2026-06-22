<?php

namespace Tests\Unit\Services\Portfolio;

use App\Exceptions\Portfolio\DuplicatePortfolioItemException;
use App\Exceptions\Portfolio\EstateNotPublishedException;
use App\Exceptions\Portfolio\InvalidPortfolioStatusTransitionException;
use App\Exceptions\Portfolio\PortfolioItemNotFoundException;
use App\Models\Estate;
use App\Models\InvestmentPortfolio;
use App\Models\PortfolioItem;
use App\Models\PortfolioProperty;
use App\Models\User;
use App\Services\Portfolio\PortfolioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioServiceTest extends TestCase
{
    use RefreshDatabase;

    private PortfolioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PortfolioService::class);
    }

    public function test_ensure_default_portfolio_creates_one_default(): void
    {
        $user = User::factory()->create();

        $portfolio = $this->service->ensureDefaultPortfolio($user);

        $this->assertTrue($portfolio->is_default);
        $this->assertSame($user->id, $portfolio->user_id);
        $this->assertSame(1, InvestmentPortfolio::query()->where('user_id', $user->id)->count());

        $again = $this->service->ensureDefaultPortfolio($user);

        $this->assertSame($portfolio->id, $again->id);
    }

    public function test_create_portfolio_with_investment_fields(): void
    {
        $user = User::factory()->create();

        $portfolio = $this->service->createPortfolio($user, [
            'name' => 'Growth Fund',
            'description' => 'High yield rentals',
            'target_budget' => 1_000_000,
            'risk_level' => 'high',
            'status' => InvestmentPortfolio::STATUS_ACTIVE,
        ]);

        $this->assertSame('Growth Fund', $portfolio->name);
        $this->assertSame('1000000.00', $portfolio->target_budget);
        $this->assertSame('high', $portfolio->risk_level);
    }

    public function test_add_estate_to_portfolio_with_invested_status(): void
    {
        $user = User::factory()->create();
        $estate = Estate::factory()->create(['status' => 'active']);

        $item = $this->service->addEstateToPortfolio($user, $estate, [
            'status' => PortfolioProperty::STATUS_INVESTED,
            'investment_amount' => 100_000,
        ]);

        $this->assertSame(PortfolioProperty::STATUS_INVESTED, $item->status);
        $this->assertNotNull($item->invested_at);
        $this->assertDatabaseHas('portfolio_properties', [
            'estate_id' => $estate->id,
            'status' => PortfolioProperty::STATUS_INVESTED,
        ]);
    }

    public function test_add_estate_rejects_non_active_estate(): void
    {
        $user = User::factory()->create();
        $estate = Estate::factory()->pending()->create();

        $this->expectException(EstateNotPublishedException::class);

        $this->service->addEstateToPortfolio($user, $estate);
    }

    public function test_add_estate_rejects_duplicate(): void
    {
        $user = User::factory()->create();
        $estate = Estate::factory()->create(['status' => 'active']);

        $this->service->addEstateToPortfolio($user, $estate);

        $this->expectException(DuplicatePortfolioItemException::class);

        $this->service->addEstateToPortfolio($user, $estate);
    }

    public function test_calculate_total_invested_amount(): void
    {
        $user = User::factory()->create();
        $portfolio = InvestmentPortfolio::factory()->forUser($user)->default()->create();

        $estateA = Estate::factory()->create(['status' => 'active']);
        $estateB = Estate::factory()->create(['status' => 'active']);

        PortfolioItem::factory()->forPortfolio($portfolio)->forEstate($estateA)->invested(100_000)->create();
        PortfolioItem::factory()->forPortfolio($portfolio)->forEstate($estateB)->invested(50_000)->create();

        $total = $this->service->calculateTotalInvestedAmount($user, $portfolio);

        $this->assertSame(150_000.0, $total);
    }

    public function test_remove_estate_from_portfolio(): void
    {
        $user = User::factory()->create();
        $estate = Estate::factory()->create(['status' => 'active']);
        $this->service->addEstateToPortfolio($user, $estate);

        $this->service->removeEstateFromPortfolio($user, $estate);

        $this->assertDatabaseMissing('portfolio_properties', [
            'estate_id' => $estate->id,
        ]);
    }

    public function test_remove_estate_throws_when_missing(): void
    {
        $user = User::factory()->create();
        $estate = Estate::factory()->create(['status' => 'active']);

        $this->expectException(PortfolioItemNotFoundException::class);

        $this->service->removeEstateFromPortfolio($user, $estate);
    }

    public function test_status_transition_tracking_to_invested_to_sold(): void
    {
        $user = User::factory()->create();
        $estate = Estate::factory()->create(['status' => 'active']);
        $item = $this->service->addEstateToPortfolio($user, $estate);

        $invested = $this->service->updatePortfolioItemStatus(
            $user,
            $item,
            PortfolioProperty::STATUS_INVESTED,
            ['investment_amount' => 200_000],
        );

        $this->assertSame(PortfolioProperty::STATUS_INVESTED, $invested->status);

        $sold = $this->service->updatePortfolioItemStatus(
            $user,
            $invested,
            PortfolioProperty::STATUS_SOLD,
        );

        $this->assertSame(PortfolioProperty::STATUS_SOLD, $sold->status);
        $this->assertNotNull($sold->sold_at);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $user = User::factory()->create();
        $estate = Estate::factory()->create(['status' => 'active']);
        $item = $this->service->addEstateToPortfolio($user, $estate);

        $this->expectException(InvalidPortfolioStatusTransitionException::class);

        $this->service->updatePortfolioItemStatus($user, $item, PortfolioProperty::STATUS_SOLD);
    }
}
