<?php
/*
|--------------------------------------------------------------------------
| EstateController — قلب المنصة: إدارة العقارات
|--------------------------------------------------------------------------
|
| الغرض:
| - عرض دليل العقارات النشطة (active) للزوار
| - إنشاء/تعديل/حذف عقارات للمالك
| - رفع صور/فيديو/إعلانات ضمن نفس الطلب أو لاحقاً
| - حساب مؤشرات الاستثمار: ROI، فترة الاسترداد، الدخل السنوي المتوقع
|
| دورة حياة العقار (status):
| - pending عند الإنشاء → يحتاج موافقة إدارة → active للعرض العام
|
| الارتباطات:
| - Models: Estate, SocialLink (morph), Estates_image/video, Estate_ads
| - Requests: StoreEstateRequest, UpdateEstateRequest
| - FileUploadService, FormatsEstateResponse
| - DB::transaction عند الإنشاء لضمان اتساق البيانات
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesOwnedCompany;
use App\Http\Requests\StoreEstateRequest;
use App\Http\Requests\UpdateEstateRequest;
use App\Models\Agent;
use App\Models\Estate;
use App\Models\PortfolioProperty;
use App\Services\EstateImageService;
use App\Services\FileUploadService;
use App\Services\Investment\InvestmentCalculatorService;
use App\Services\SocialLinkService;
use App\Traits\FormatsEstateResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstateController extends BaseApiController
{
    use FormatsEstateResponse, ResolvesOwnedCompany;

    /** حقن خدمة رفع الملفات */
    public function __construct(
        private readonly FileUploadService $uploader,
        private readonly SocialLinkService $socialLinks,
        private readonly EstateImageService $estateImages,
        private readonly InvestmentCalculatorService $investmentCalculator,
    ) {}

    /**
     * إعدادات الخريطة لـ Vue + Leaflet (مركز افتراضي، zoom، طبقة OSM).
     * لا يتطلب توكن — عام.
     */
    public function mapConfig(): JsonResponse
    {
        return $this->successResponse([
            'default_center' => [
                'latitude' => config('realestate.map.default_lat'),
                'longitude' => config('realestate.map.default_lng'),
            ],
            'default_zoom' => config('realestate.map.default_zoom'),
            'tile_layer' => [
                'url' => config('realestate.map.tile_url'),
                'attribution' => config('realestate.map.attribution'),
            ],
        ], 'Map configuration retrieved.');
    }

    /**
     * علامات الخريطة للعقارات النشطة التي لها إحداثيات.
     *
     * Query اختياري (bounding box من Leaflet getBounds()):
     * north, south, east, west
     */
    public function mapMarkers(Request $request): JsonResponse
    {
        $query = Estate::query()
            ->where('status', 'active')
            ->withCoordinates()
            ->with(['place.city']);

        if ($request->hasAny(['north', 'south', 'east', 'west'])) {
            $validated = $request->validate([
                'north' => ['required', 'numeric', 'between:-90,90'],
                'south' => ['required', 'numeric', 'between:-90,90', 'lte:north'],
                'east' => ['required', 'numeric', 'between:-180,180'],
                'west' => ['required', 'numeric', 'between:-180,180'],
            ]);

            $query->withinMapBounds(
                (float) $validated['north'],
                (float) $validated['south'],
                (float) $validated['east'],
                (float) $validated['west'],
            );
        }

        $markers = $query
            ->orderBy('id')
            ->get(['id', 'name', 'price', 'latitude', 'longitude', 'type_text', 'kind_text', 'places_id']);

        return $this->successResponse(
            $markers->map(fn (Estate $estate) => $this->formatMapMarker($estate))->values()->all(),
            'Map markers retrieved.',
        );
    }

    public function index(Request $request): JsonResponse
    {
        $query = Estate::query()
            ->where('status', 'active')
            ->with(['place.city', 'user:id,username,fname,lname,country_code_phone', 'images']);

        $this->applyFilters($query, $request);

        $estates = $query->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $estates->through(fn (Estate $e) => $this->formatEstate($e))->items(),
            'Estates retrieved.',
            200,
            $this->paginationMeta($estates),
        );
    }

    /**
     * مسؤول عن عرض تفاصيل عقار واحد.
     *
     * - غير active: هل يمكن مشاهدة العقار(canViewEstate)
     * هل هذا المستخدم يملك حق مشاهدة هذا العقار؟
     */
    public function show(Request $request, Estate $estate): JsonResponse
    {
        //يتحقق من صلاحية العرض للعقار بناء على المستخدم والحالة الحالية للعقار
        if (! $this->canViewEstate($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }
        /*
        النظام يريد ان يعرف كم شخص شاهد العقار 
        لاكن لا يريد زيادة المشاهدات عندما صاحب العقار يشاهد العقار 
        ! $this->isOwner(...) ليس صاحب العقار 
        */
        if ($estate->status === 'active' && ! $this->isOwner($request, $estate)) {
            $estate->increment('views');
        }
        /**
         * يقوم بتحميل العقار بناء على المعلومات المطلوبة
         *
         */
        $estate->load(['place.city', 'user:id,username,fname,lname,country_code_phone', 'socialLinks', 'images', 'videos', 'ads']);

        $metrics = $this->investmentCalculator->calculateForEstate($estate);
        $data = $this->formatEstate($estate);
        $data['expected_annual_income'] = $metrics->expectedAnnualIncome;
        $data['roi'] = $metrics->roi;
        $data['payback_period'] = $metrics->paybackPeriod;

        return $this->successResponse(
            $data,
            'Estate retrieved.',
        );
    }

    public function portfolioStatus(Request $request, Estate $estate): JsonResponse
    {
        $item = PortfolioProperty::query()
            ->where('estate_id', $estate->id)
            ->whereHas('portfolio', fn ($q) => $q->where('user_id', $request->user()->id))
            ->first(['status']);

        $globalTaken = PortfolioProperty::query()
            ->where('estate_id', $estate->id)
            ->whereIn('status', [PortfolioProperty::STATUS_INVESTED, PortfolioProperty::STATUS_SOLD])
            ->exists();

        return $this->successResponse([
            'portfolio_status' => $item?->status,
            'global_taken' => $globalTaken,
            'can_add' => ! $globalTaken,
        ]);
    }

    /** عقارات المستخدم المسجّل (كل الحالات) */
    public function myEstates(Request $request): JsonResponse
    {
        $estates = $request->user()
            ->estates()
            ->with(['place.city', 'images', 'videos', 'ads']);

        if ($request->filled('search')) {
            $search = $request->search;
            $estates->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $estates->where('status', $request->status);
        }

        if ($request->filled('type_text')) {
            $estates->where('type_text', $request->type_text);
        }

        if ($request->filled('kind_text')) {
            $estates->where('kind_text', $request->kind_text);
        }

        $estates = $estates->latest()->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $estates->through(fn (Estate $e) => $this->formatEstate($e))->items(),
            'Your estates retrieved.',
            200,
            $this->paginationMeta($estates),
        );
    }

    /**
     * إنشاء عقار جديد داخل Transaction.
     * transaction تعني قبول كل العمليات او الغاء كل العمليات 
     * يؤدي الى فشل رفع الصورة الى حذف جميع العقارات السابقة  وكأن شيء لم يحدث
     * 
     */
    public function store(StoreEstateRequest $request): JsonResponse
    {
        $data = $this->prepareEstateData($request); //يقوم بتحضير البيانات للعقار

        $estate = DB::transaction(function () use ($request, $data) {
            $data['user_id'] = $request->user()->id;
            $data['status'] = 'pending';
             /***
             * يقوم بحساب الاستثمار في العقار بناء على الحقول المطلوبة
             * أي أن العقار يُحفظ ومعه الحسابات الاستثمارية جاهزة.
             * الحساب هنا
             */
            $this->investmentCalculator->applyToEstatePayload($data);

            $estate = Estate::create($data);

            $this->socialLinks->syncFromRequest($estate, $request->only(['facebook', 'instagram', 'links']));

            $this->syncUploadedMedia($request, $estate);

            return $estate;
        });

        return $this->createdResponse(
            $this->formatEstate($estate->load(['place.city', 'socialLinks', 'images', 'videos', 'ads'])),
            'Estate created successfully. It will be visible once approved.',
        );
    }

    /**
     * تحديث عقار — للمالك فقط.
     */
    public function update(UpdateEstateRequest $request, Estate $estate): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        $data = $this->prepareEstateData($request, includeSocial: true);

        if ($request->has('facebook') || $request->has('instagram') || $request->has('links')) {
            $this->socialLinks->syncFromRequest(
                $estate,
                $request->only(['facebook', 'instagram', 'links']),
                replace: true,
            );
        }

        $this->investmentCalculator->applyToEstatePayload($data, $estate);
        $estate->update($data);
        $this->syncUploadedMedia($request, $estate);

        return $this->successResponse(
            $this->formatEstate($estate->fresh()->load(['place.city', 'socialLinks', 'images', 'videos', 'ads'])),
            'Estate updated successfully.',
        );
    }

    /** حذف عقار + حذف ملفات الوسائط من التخزين */
    public function destroy(Request $request, Estate $estate): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        $this->deleteEstateMediaFiles($estate);
        $estate->delete();

        return $this->deletedResponse('Estate deleted successfully.');
    }

    /** تسجيل مشاركة — للعقارات active فقط */
    public function share(Estate $estate): JsonResponse
    {
        if ($estate->status !== 'active') {
            return $this->notFoundResponse('Estate not found.');
        }

        $estate->increment('shares');

        return $this->successResponse(
            ['shares' => $estate->fresh()->shares],
            'Share recorded.',
        );
    }

    /**
     * تطبيق فلاتر البحث على استعلام العقارات.
     */
    private function applyFilters($query, Request $request): void
    {
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

        if ($request->filled('rent_kind')) {
            $query->where('rent_kind', $request->rent_kind);
        }

        if ($request->has('is_furnished')) {
            $query->where('is_furnished', $request->boolean('is_furnished'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('min_bedrooms')) {
            $query->where('num_of_bedrooms', '>=', $request->integer('min_bedrooms'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'latest');

        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareEstateData(Request $request, bool $includeSocial = false): array
    {
        $except = ['images', 'videos', 'ads', 'primary_image_index', 'main_ad_index'];

        if ($includeSocial) {
            $except[] = 'facebook';//
            $except[] = 'instagram';
        } else {
            $except = array_merge($except, ['facebook', 'instagram']);
        }

        return $request->safe()->except($except);
    }

    private function syncUploadedMedia(Request $request, Estate $estate): void
    {
        if ($request->hasFile('images')) {
            $primaryIndex = $request->integer('primary_image_index', 0);

            foreach ($request->file('images') as $index => $file) {
                $this->estateImages->createImage(
                    $estate,
                    $file,
                    $index === $primaryIndex,
                );
            }
        }

        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $file) {
                $path = $this->uploader->storeFile($file, 'estates/'.$estate->id.'/videos');
                $estate->videos()->create(['video' => $path]);
            }
        }

        if ($request->hasFile('ads')) {
            $mainIndex = $request->integer('main_ad_index', 0);

            foreach ($request->file('ads') as $index => $file) {
                $path = $this->uploader->storeImage($file, 'estates/'.$estate->id.'/ads');
                $isMain = $index === $mainIndex;

                if ($isMain) {
                    $estate->ads()->update(['is_main' => false]);
                }

                $estate->ads()->create([
                    'image' => $path,
                    'is_main' => $isMain,
                ]);
            }
        }
    }

    private function deleteEstateMediaFiles(Estate $estate): void
    {
        $estate->load(['images', 'videos', 'ads']);

        foreach ($estate->images as $image) {
            $this->estateImages->deleteImage($image);
        }

        foreach ($estate->videos as $video) {
            $this->uploader->deleteIfExists($video->video);
        }

        foreach ($estate->ads as $ad) {
            $this->uploader->deleteIfExists($ad->image);
        }
    }

    /** عقارات الشركة ووسطائها — نقطة نهاية موحّدة */
    public function companyAllEstates(Request $request): JsonResponse
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return $this->notFoundResponse('Company not found.');
        }

        $userIds = collect([$request->user()->id]);

        $agentUserIds = Agent::where('companies_id', $company->id)
            ->pluck('user_id');

        $userIds = $userIds->merge($agentUserIds)->unique();

        $estates = Estate::whereIn('user_id', $userIds)
            ->with(['place.city', 'images', 'videos', 'ads', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $estates->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $estates->where('status', $request->status);
        }

        if ($request->filled('type_text')) {
            $estates->where('type_text', $request->type_text);
        }

        if ($request->filled('kind_text')) {
            $estates->where('kind_text', $request->kind_text);
        }

        if ($request->filled('agent_id')) {
            $estates->where('user_id', $request->agent_id);
        }

        $estates = $estates->latest()->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $estates->through(fn (Estate $e) => $this->formatEstate($e))->items(),
            'Company estates retrieved.',
            200,
            $this->paginationMeta($estates),
        );
    }

    private function canViewEstate(Request $request, Estate $estate): bool
    {
        if ($estate->status === 'active') {
            return true;
        }

        return $this->isOwner($request, $estate);
    }

    private function isOwner(Request $request, Estate $estate): bool
    {
        return $request->user() && $estate->user_id === $request->user()->id;
    }
}
