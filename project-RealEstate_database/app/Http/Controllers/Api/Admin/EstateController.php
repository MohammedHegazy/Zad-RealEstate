<?php
/*
|--------------------------------------------------------------------------
| EstateController — إدارة العقارات من لوحة المدير
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Concerns\PersistsEstateFields;
use App\Http\Requests\Admin\StoreAdminEstateRequest;
use App\Http\Requests\Admin\UpdateAdminEstateRequest;
use App\Http\Requests\Admin\UpdateEstateStatusRequest;
use App\Models\Estate;
use App\Models\Notifications;
use App\Services\EstateImageService;
use App\Services\FileUploadService;
use App\Services\Investment\InvestmentCalculatorService;
use App\Services\SocialLinkService;
use App\Traits\FormatsEstateResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstateController extends BaseApiController
{
    use FormatsEstateResponse, PersistsEstateFields;

    public function __construct(
        private readonly FileUploadService $uploader,
        private readonly SocialLinkService $socialLinks,
        private readonly EstateImageService $estateImages,
        private readonly InvestmentCalculatorService $investmentCalculator,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Estate::query()
            ->with(['user:id,username,fname,lname,country_code_phone', 'place:id,name,cities_id', 'images']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('places_id')) {
            $query->where('places_id', $request->places_id);
        }

        if ($request->filled('cities_id')) {
            $query->whereHas('place', fn ($q) => $q->where('cities_id', $request->cities_id));
        }

        if ($request->filled('type_text')) {
            $query->where('type_text', $request->type_text);
        }

        if ($request->filled('kind_text')) {
            $query->where('kind_text', $request->kind_text);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $estates = $query->latest()->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $estates->through(fn (Estate $e) => $this->formatEstate($e))->items(),
            'Estates retrieved.',
            200,
            $this->paginationMeta($estates),
        );
    }

    public function store(StoreAdminEstateRequest $request): JsonResponse
    {
        $data = $this->prepareEstatePayload($request);

        $estate = DB::transaction(function () use ($request, $data) {
            $data['user_id'] = $request->integer('user_id');
            $data['status'] = $request->input('status', 'pending');

            $this->applyEstateInvestmentMetrics($data, calculator: $this->investmentCalculator);

            $estate = Estate::create($data);

            $this->socialLinks->syncFromRequest($estate, $request->only(['facebook', 'instagram', 'links']));
            $this->syncEstateUploadedMedia($request, $estate, $this->uploader, $this->estateImages);

            return $estate;
        });

        return $this->createdResponse(
            $this->formatEstate($estate->load(['user', 'place.city', 'socialLinks', 'images', 'videos', 'ads'])),
            'Estate created successfully.',
        );
    }

    public function show(Estate $estate): JsonResponse
    {
        $estate->load(['user', 'place.city', 'socialLinks', 'images', 'videos', 'ads']);

        return $this->successResponse(
            $this->formatEstate($estate),
            'Estate retrieved.',
        );
    }

    public function update(UpdateAdminEstateRequest $request, Estate $estate): JsonResponse
    {
        $previousStatus = $estate->status;
        $data = $this->prepareEstatePayload($request, includeSocial: true);

        if ($request->has('facebook') || $request->has('instagram') || $request->has('links')) {
            $this->socialLinks->syncFromRequest(
                $estate,
                $request->only(['facebook', 'instagram', 'links']),
                replace: true,
            );
        }

        $this->applyEstateInvestmentMetrics($data, $estate, $this->investmentCalculator);
        $estate->update($data);
        $this->syncEstateUploadedMedia($request, $estate, $this->uploader, $this->estateImages);

        if (
            isset($data['status'])
            && $data['status'] !== $previousStatus
        ) {
            $this->notifyOwnerAboutStatus($estate, $previousStatus, $data['status']);
        }

        return $this->successResponse(
            $this->formatEstate($estate->fresh()->load(['user', 'place.city', 'socialLinks', 'images', 'videos', 'ads'])),
            'Estate updated successfully.',
        );
    }

    public function updateStatus(UpdateEstateStatusRequest $request, Estate $estate): JsonResponse
    {
        $previous = $estate->status;
        $estate->update(['status' => $request->status]);

        $this->notifyOwnerAboutStatus($estate, $previous, $request->status);

        return $this->successResponse(
            $this->formatEstate($estate->fresh()->load(['user', 'place', 'images'])),
            'Estate status updated successfully.',
        );
    }

    public function destroy(Estate $estate): JsonResponse
    {
        $this->deleteEstateMediaFiles($estate);
        $estate->delete();

        return $this->deletedResponse('Estate deleted successfully.');
    }

    private function notifyOwnerAboutStatus(Estate $estate, string $previous, string $new): void
    {
        if ($previous === $new) {
            return;
        }

        $labels = [
            'pending' => 'قيد المراجعة',
            'active' => 'مفعّل',
            'rejected' => 'مرفوض',
        ];

        $label = $labels[$new] ?? $new;

        Notifications::create([
            'user_id' => $estate->user_id,
            'content' => "تم تحديث حالة عقارك «{$estate->name}» إلى: {$label}.",
            'is_read' => false,
        ]);
    }

    private function deleteEstateMediaFiles(Estate $estate): void
    {
        $estate->load(['images', 'videos', 'ads']);

        foreach ($estate->images as $image) {
            $this->uploader->deleteIfExists($image->image);
        }

        foreach ($estate->videos as $video) {
            $this->uploader->deleteIfExists($video->video);
        }

        foreach ($estate->ads as $ad) {
            $this->uploader->deleteIfExists($ad->image);
        }
    }
}
