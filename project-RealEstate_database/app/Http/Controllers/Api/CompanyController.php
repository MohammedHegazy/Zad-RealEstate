<?php
/*
|--------------------------------------------------------------------------
| CompanyController — الشركات العقارية
|--------------------------------------------------------------------------
|
| الغرض:
| - دليل شركات عام، إدارة شركة المستخدم (ملف واحد لكل مستخدم)
| - رفع صور profile و banner
| - منع حذف شركة لديها وسطاء
|
| الارتباطات:
| - FileUploadService
| - FormatsCompanyResponse
| - Model Companies
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesOwnedCompany;
use App\Http\Requests\StoreMyCompanyRequest;
use App\Http\Requests\UpdateMyCompanyRequest;
use App\Models\Companies;
use App\Services\FileUploadService;
use App\Traits\FormatsCompanyResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends BaseApiController
{
    use FormatsCompanyResponse, ResolvesOwnedCompany;

    public function __construct(
        private readonly FileUploadService $uploader,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Companies::query()
            ->approved()
            ->with(['place.city', 'user:id,username,fname,lname', 'socialLinks']);

        if ($request->filled('places_id')) {
            $query->where('places_id', $request->places_id);
        }

        if ($request->filled('search')) {
            $query->where('company_name', 'like', '%'.$request->search.'%');
        }

        $sort = $request->input('sort', 'newest');
        $query = match ($sort) {
            'trust' => $query->orderBy('trust_score', 'desc'),
            'employees' => $query->orderBy('employees_num', 'desc'),
            default => $query,
        };
        $companies = $query->latest()->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $companies->through(fn (Companies $c) => $this->formatCompany($c))->items(),
            'Companies retrieved.',
            200,
            $this->paginationMeta($companies),
        );
    }

    public function myCompany(Request $request): JsonResponse
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return $this->notFoundResponse('You do not have a company profile.');
        }

        $company->load(['place.city', 'socialLinks', 'agents.user:id,username,fname,lname']);

        return $this->successResponse(
            $this->formatCompany($company),
            'Your company profile retrieved.',
        );
    }

    public function store(StoreMyCompanyRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['profile_image', 'banner_image', 'status']);
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'pending';

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $this->uploader->storeImage(
                $request->file('profile_image'),
                'companies/profile'
            );
        }

        if ($request->hasFile('banner_image')) {
            $data['banner_image'] = $this->uploader->storeImage(
                $request->file('banner_image'),
                'companies/banner'
            );
        }

        $company = Companies::create($data);

        return $this->createdResponse(
            $this->formatCompany($company->load(['place.city', 'socialLinks'])),
            'Company created successfully. It will be visible once approved.',
        );
    }

    public function show(Companies $company): JsonResponse
    {
        if (! $company->isPubliclyVisible()) {
            return $this->notFoundResponse('Company not found.');
        }

        return $this->successResponse(
            $this->formatCompany($company->load(['place.city', 'user:id,username,fname,lname', 'socialLinks', 'agents.user'])),
            'Company profile retrieved.',
        );
    }

    public function update(UpdateMyCompanyRequest $request): JsonResponse
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return $this->notFoundResponse('You do not have a company profile.');
        }

        $data = $request->safe()->except(['profile_image', 'banner_image', 'status']);

        if ($request->hasFile('profile_image')) {
            $this->uploader->deleteIfExists($company->profile_image);
            $data['profile_image'] = $this->uploader->storeImage(
                $request->file('profile_image'),
                'companies/profile'
            );
        }

        if ($request->hasFile('banner_image')) {
            $this->uploader->deleteIfExists($company->banner_image);
            $data['banner_image'] = $this->uploader->storeImage(
                $request->file('banner_image'),
                'companies/banner'
            );
        }

        $company->update($data);

        return $this->successResponse(
            $this->formatCompany($company->fresh()->load(['place.city', 'socialLinks', 'agents'])),
            'Company updated successfully.',
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return $this->notFoundResponse('You do not have a company profile.');
        }

        if ($company->agents()->exists()) {
            return $this->errorResponse('Cannot delete a company that has agents. Remove agents first.', 422);
        }

        $this->uploader->deleteIfExists($company->profile_image);
        $this->uploader->deleteIfExists($company->banner_image);
        $company->delete();

        return $this->deletedResponse('Company deleted successfully.');
    }
}
