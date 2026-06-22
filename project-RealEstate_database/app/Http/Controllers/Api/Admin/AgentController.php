<?php
/*
|--------------------------------------------------------------------------
| AgentController — إدارة الوسطاء العقاريين (CRUD للمدير)
|--------------------------------------------------------------------------
| الوسطاء يربطون مستخدم (user) بشركة (company) مع صورة وإحصائيات مشاهدات/مشاركات.
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Concerns\ManagesAgentProfiles;
use App\Http\Controllers\Concerns\QueriesAgents;
use App\Http\Requests\Admin\StoreAgentRequest;
use App\Http\Requests\Admin\UpdateAdminAgentRequest;
use App\Models\Agent;
use App\Traits\FormatsAgentResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentController extends BaseApiController
{
    use FormatsAgentResponse, ManagesAgentProfiles, QueriesAgents;

    public function index(Request $request): JsonResponse
    {
        $agents = $this->agentsQuery($request)->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $agents->through(fn (Agent $agent) => $this->formatAgent($agent))->items(),
            'Agents retrieved.',
            200,
            $this->paginationMeta($agents),
        );
    }

    public function store(StoreAgentRequest $request): JsonResponse
    {
        $data = $this->agentDataFromRequest($request);
        $data['user_id'] = $request->validated('user_id');
        $data['companies_id'] = $request->validated('companies_id');
        $data['views'] = 0;
        $data['shares'] = 0;

        $agent = Agent::create($data);

        return $this->createdResponse(
            $this->formatAgent($agent->load(['user', 'company'])),
            'Agent created successfully.',
        );
    }

    public function show(Agent $agent): JsonResponse
    {
        $agent->load(['user', 'company', 'socialLinks', 'approvedReviews.user:id,username,fname,lname'])
            ->loadAvg('approvedReviews', 'rating')
            ->loadCount('approvedReviews');

        return $this->successResponse(
            $this->formatAgent($agent),
            'Agent retrieved.',
        );
    }

    public function update(UpdateAdminAgentRequest $request, Agent $agent): JsonResponse
    {
        $agent->update($this->agentDataFromRequest($request, $agent));

        return $this->successResponse(
            $this->formatAgent($agent->fresh()->load(['user', 'company'])),
            'Agent updated successfully.',
        );
    }

    public function destroy(Agent $agent): JsonResponse
    {
        $this->deleteAgentProfileImage($agent);
        $agent->delete();

        return $this->deletedResponse('Agent deleted successfully.');
    }
}
