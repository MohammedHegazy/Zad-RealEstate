<?php
/*
|--------------------------------------------------------------------------
| CompanyController — إدارة الشركات العقارية (CRUD للمدير)
|--------------------------------------------------------------------------
| الشركة مرتبطة بمستخدم (مالك) ومنطقة (place).
| رفع الصور: profile_image و banner_image عبر FileUploadService.
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\StoreCompanyRequest;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Http\Requests\Admin\UpdateCompanyStatusRequest;
use App\Models\Companies;
use App\Models\Notifications;
use App\Services\FileUploadService;
use App\Traits\FormatsCompanyResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends BaseApiController
{
    use FormatsCompanyResponse;

    public function __construct(
        private readonly FileUploadService $uploader,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Companies::query()->with(['user:id,username,fname,lname,country_code_phone', 'place:id,name,cities_id']);

        if ($request->filled('search')) {
            $query->where('company_name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('places_id')) {
            $query->where('places_id', $request->places_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $companies = $query->latest()->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $companies->through(fn (Companies $c) => $this->formatCompany($c))->items(),
            'Companies retrieved.',
            200,
            $this->paginationMeta($companies),
        );
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['profile_image', 'banner_image']);

        $profileImage = $request->file('profile_image');
        if ($profileImage && $profileImage->isValid()) {
            $data['profile_image'] = $this->uploader->storeImage(
                $profileImage,
                'companies/profile'
            );
        }

        $bannerImage = $request->file('banner_image');
        if ($bannerImage && $bannerImage->isValid()) {
            $data['banner_image'] = $this->uploader->storeImage(
                $bannerImage,
                'companies/banner'
            );
        }

        $company = Companies::create($data);

        return $this->createdResponse(
            $this->formatCompany($company->load(['user', 'place'])),
            'Company created successfully.',
        );
    }

    public function show(Companies $company): JsonResponse
    {
        return $this->successResponse(
            $this->formatCompany($company->load(['user', 'place.city', 'agents.user', 'socialLinks'])),
            'Company retrieved.',
        );
    }

    public function update(UpdateCompanyRequest $request, Companies $company): JsonResponse
    {
        $data = $request->safe()->except(['profile_image', 'banner_image']);

        $profileImage = $request->file('profile_image');
        if ($profileImage && $profileImage->isValid()) {
            $this->uploader->deleteIfExists($company->profile_image);
            $data['profile_image'] = $this->uploader->storeImage(
                $profileImage,
                'companies/profile'
            );
        }

        $bannerImage = $request->file('banner_image');
        if ($bannerImage && $bannerImage->isValid()) {
            $this->uploader->deleteIfExists($company->banner_image);
            $data['banner_image'] = $this->uploader->storeImage(
                $bannerImage,
                'companies/banner'
            );
        }

        $company->update($data);

        return $this->successResponse(
            $this->formatCompany($company->fresh()->load(['user', 'place'])),
            'Company updated successfully.',
        );
    }

    public function updateStatus(UpdateCompanyStatusRequest $request, Companies $company): JsonResponse
    {
        $previous = $company->status;
        $company->update(['status' => $request->status]);

        $this->notifyOwnerAboutStatus($company, $previous, $request->status);

        return $this->successResponse(
            $this->formatCompany($company->fresh()->load(['user', 'place'])),
            'Company status updated successfully.',
        );
    }

    public function destroy(Companies $company): JsonResponse
    {
        if ($company->agents()->exists()) {
            return $this->errorResponse('Cannot delete: company has agents. Reassign or remove agents first.', 422);
        }

        $this->uploader->deleteIfExists($company->profile_image);
        $this->uploader->deleteIfExists($company->banner_image);
        $company->delete();

        return $this->deletedResponse('Company deleted successfully.');
    }

    private function notifyOwnerAboutStatus(Companies $company, string $previous, string $new): void
    {
        if ($previous === $new) {
            return;
        }

        $labels = [
            'pending' => 'قيد المراجعة',
            'approved' => 'موافق عليها',
            'rejected' => 'مرفوضة',
            'suspended' => 'معلّقة',
        ];

        $label = $labels[$new] ?? $new;

        Notifications::create([
            'user_id' => $company->user_id,
            'content' => "تم تحديث حالة شركتك «{$company->company_name}» إلى: {$label}.",
            'is_read' => false,
        ]);
    }
}
