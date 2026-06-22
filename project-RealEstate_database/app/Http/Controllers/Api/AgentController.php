<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ManagesAgentProfiles;//
use App\Http\Controllers\Concerns\QueriesAgents;
use App\Http\Controllers\Concerns\ResolvesOwnedCompany;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Agent;
use App\Models\Companies;   
use App\Traits\FormatsAgentResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentController extends BaseApiController
{
    use FormatsAgentResponse, ManagesAgentProfiles, QueriesAgents, ResolvesOwnedCompany;

    public function index(Request $request): JsonResponse //عرض جميع الوسطاء   GET/AGENTS
    {
        $agents = $this->agentsQuery($request)->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $agents->through(fn (Agent $agent) => $this->formatAgent($agent))->items(),
            'Agents retrieved.',
            200,
            $this->paginationMeta($agents),
        );
    }

    public function show(Request $request, Agent $agent): JsonResponse//عرض بيانات وسيط معين  GET/AGENTS/{ID}
    {
        $agent->increment('views');
        $agent->refresh();

        $agent->load(['user:id,username,fname,lname,email,phone,type', 'company:id,company_name,places_id'])//لجلب بيانات المستخدم والشركة
            ->loadAvg('approvedReviews', 'rating')//لجلب متوسط التقييمات الموثوق بها
            ->loadCount('approvedReviews');//لجلب عدد التقييمات الموثوق بها

        return $this->successResponse(//عرض البيانات بنجاح
            $this->formatAgent($agent),//لتنسيق البيانات للعرض
            'Agent profile retrieved.',//رسالة النجاح
        );
    }

    public function share(Agent $agent): JsonResponse//مشاركة وسيط POST/AGENTS/{ID}/SHARE
    {
        $agent->increment('shares');//زيادة عدد المشاركات

        return $this->successResponse(
            ['shares' => $agent->fresh()->shares],//عرض عدد المشاركات
            'Share recorded.',//رسالة النجاح
        );
    }

    public function indexByCompany(Request $request, Companies $company): JsonResponse//عرض جميع الوسطاء لشركة معينة  GET/COMPANIES/{ID}/AGENTS
    {
        $request->merge(['companies_id' => $company->id]);//لجلب بيانات الشركة

        return $this->index($request);//عرض جميع الوسطاء لشركة معينة
    }

    public function myProfile(Request $request): JsonResponse//عرض بيانات وسيط الشخصي  GET/AGENTS/MY-PROFILE
    {
        $agent = $request->user()->agent;//لجلب بيانات الوسيط

        if (! $agent) {//للتحقق من ان الوسيط موجود
            return $this->notFoundResponse('You do not have an agent profile.');//رسالة الخطأ
        }

        $agent->load(['user:id,username,fname,lname,email,phone,type', 'company:id,company_name,places_id'])//لجلب بيانات المستخدم والشركة
            ->loadAvg('approvedReviews', 'rating')//لجلب متوسط التقييمات الموثوق بها
            ->loadCount('approvedReviews');//لجلب عدد التقييمات الموثوق بها

        return $this->successResponse(
            $this->formatAgent($agent),//عرض البيانات بنجاح
            'Your agent profile retrieved.',//رسالة النجاح
        );
    }

    public function updateMyProfile(UpdateAgentRequest $request): JsonResponse//تعديل بيانات وسيط الشخصي  PUT/AGENTS/MY-PROFILE 
    {
        $agent = $request->user()->agent;//لجلب بيانات الوسيط

        if (! $agent) {//للتحقق من ان الوسيط موجود
            return $this->notFoundResponse('You do not have an agent profile.');//رسالة الخطأ
        }

        $agent->update($this->agentDataFromRequest($request, $agent));//تعديل بيانات الوسيط

        return $this->successResponse(
            $this->formatAgent($agent->fresh()->load(['user', 'company'])),
            'Agent profile updated successfully.',
        );
    }

    public function indexForCompany(Request $request): JsonResponse//عرض جميع الوسطاء لشركة المستخدم  GET/MY/COMPANY/AGENTS
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return $this->notFoundResponse('Company not found.');
        }

        $request->merge(['companies_id' => $company->id]);

        return $this->index($request);
    }

    public function storeForCompany(StoreAgentRequest $request): JsonResponse//اضافة وسيط لشركة المستخدم  POST/MY/COMPANY/AGENTS
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return $this->notFoundResponse('Company not found.');
        }

        $data = $this->agentDataFromRequest($request);
        $data['user_id'] = $request->validated('user_id');
        $data['companies_id'] = $company->id;
        $data['views'] = 0;
        $data['shares'] = 0;

        $agent = Agent::create($data);

        return $this->createdResponse(
            $this->formatAgent($agent->load(['user', 'company'])),
            'Agent added to company successfully.',
        );
    }

    public function updateForCompany(UpdateAgentRequest $request, Agent $agent): JsonResponse//تعديل بيانات وسيط لشركة المستخدم  PUT/MY/COMPANY/AGENTS/{ID}
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return $this->notFoundResponse('Company not found.');
        }

        if ($agent->companies_id !== $company->id) {
            return $this->notFoundResponse('Agent does not belong to this company.');
        }

        $agent->update($this->agentDataFromRequest($request, $agent));

        return $this->successResponse(
            $this->formatAgent($agent->fresh()->load(['user', 'company'])),
            'Agent updated successfully.',
        );
    }

    public function destroyForCompany(Request $request, Agent $agent): JsonResponse//حذف وسيط لشركة المستخدم  DELETE/MY/COMPANY/AGENTS/{ID}
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return $this->notFoundResponse('Company not found.');
        }

        if ($agent->companies_id !== $company->id) {
            return $this->notFoundResponse('Agent does not belong to this company.');
        }

        $this->deleteAgentProfileImage($agent);
        $agent->delete();

        return $this->deletedResponse('Agent removed from company successfully.');
    }

    public function approveForCompany(Request $request, Agent $agent): JsonResponse//اعتماد وسيط POST/MY/COMPANY/AGENTS/{ID}/APPROVE
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return $this->notFoundResponse('Company not found.');
        }

        if ($agent->companies_id !== $company->id) {
            return $this->notFoundResponse('Agent does not belong to this company.');
        }

        if ($agent->isApproved()) {
            return $this->successResponse(
                $this->formatAgent($agent->load(['user', 'company'])),
                'Agent is already approved.',
            );
        }

        $agent->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return $this->successResponse(
            $this->formatAgent($agent->fresh()->load(['user', 'company'])),
            'Agent approved successfully.',
        );
    }

    public function rejectForCompany(Request $request, Agent $agent): JsonResponse//رفض وسيط POST/MY/COMPANY/AGENTS/{ID}/REJECT
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return $this->notFoundResponse('Company not found.');
        }

        if ($agent->companies_id !== $company->id) {
            return $this->notFoundResponse('Agent does not belong to this company.');
        }

        if ($agent->isRejected()) {
            return $this->successResponse(
                $this->formatAgent($agent->load(['user', 'company'])),
                'Agent is already rejected.',
            );
        }

        $agent->update([
            'status' => 'rejected',
            'approved_at' => null,
        ]);

        return $this->successResponse(
            $this->formatAgent($agent->fresh()->load(['user', 'company'])),
            'Agent rejected successfully.',
        );
    }
}
