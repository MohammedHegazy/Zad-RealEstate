<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Concerns\QueriesAdminUsers;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Traits\FormatsAdminUserResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
    use FormatsAdminUserResponse, QueriesAdminUsers;

    public function index(Request $request): JsonResponse//
    {
        $users = $this->adminUsersQuery($request)
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $users->through(fn (User $user) => $this->formatAdminUser($user))->items(),
            'Users retrieved.',
            200,
            $this->paginationMeta($users),
        );
    }

    public function store(Request $request): JsonResponse
    {
        return $this->errorResponse('Not implemented.', 501);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['agent:id,user_id,companies_id,trust_score', 'company:id,user_id,company_name,status'])
            ->loadCount('estates');

        return $this->successResponse(
            $this->formatAdminUser($user, detailed: true),
            'User retrieved.',
        );
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if (
            isset($data['type'])
            && $user->isAdmin()
            && ! in_array($data['type'], config('realestate.admin_types', ['admin']), true)
            && $this->activeAdminCount() <= 1
        ) {
            return $this->errorResponse('Cannot change type of the last admin account.', 422);
        }

        $user->update($data);

        return $this->successResponse(
            $this->formatAdminUser($user->fresh()->loadCount('estates')),
            'User updated.',
        );
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            return $this->errorResponse('You cannot delete your own account.', 422);
        }

        if ($user->isAdmin() && $this->activeAdminCount() <= 1) {
            return $this->errorResponse('Cannot delete the last admin account.', 422);
        }

        $user->delete();

        return $this->deletedResponse('User deleted.');
    }

    private function activeAdminCount(): int
    {
        return User::query()
            ->whereIn('type', config('realestate.admin_types', ['admin']))
            ->where('status', 'active')
            ->count();
    }
}
