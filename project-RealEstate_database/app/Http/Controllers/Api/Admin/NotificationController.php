<?php
/*
|--------------------------------------------------------------------------
| NotificationController — إدارة الإشعارات داخل التطبيق (للمدير)
|--------------------------------------------------------------------------
| المدير يرسل إشعاراً لمستخدم، يطلع على الكل، يعلّم كمقروء، أو يحذف.
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\StoreNotificationRequest;
use App\Models\Notifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Notifications::query()->with('user:id,username,email,fname,lname');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('is_read')) {
            $query->where('is_read', filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN));
        }

        $notifications = $query->latest()->paginate($request->integer('per_page', 20));

        return $this->successResponse(
            $notifications->items(),
            'Notifications retrieved.',
            200,
            $this->paginationMeta($notifications),
        );
    }

    public function store(StoreNotificationRequest $request): JsonResponse
    {
        $notification = Notifications::create($request->validated());

        return $this->createdResponse(
            $notification->load('user'),
            'Notification sent successfully.',
        );
    }

    public function show(Notifications $notification): JsonResponse
    {
        return $this->successResponse(
            $notification->load('user:id,username,email,fname,lname,type'),
            'Notification retrieved.',
        );
    }

    public function markAsRead(Notifications $notification): JsonResponse
    {
        $notification->update(['is_read' => true]);

        return $this->successResponse(
            $notification->fresh()->load('user'),
            'Notification marked as read.',
        );
    }

    public function destroy(Notifications $notification): JsonResponse
    {
        $notification->delete();

        return $this->deletedResponse('Notification deleted successfully.');
    }
}
