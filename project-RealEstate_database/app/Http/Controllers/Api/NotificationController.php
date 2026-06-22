<?php
/*
|--------------------------------------------------------------------------
| NotificationController — إشعارات داخل التطبيق
|--------------------------------------------------------------------------
|
| الغرض:
| - قراءة وتحديث وحذف إشعارات المستخدم (جدول notifications)
| - عدّ غير المقروء عبر NotificationService
|
| الارتباطات:
| - Model Notifications
| - علاقة User::inAppNotifications()
| - يتطلب مصادقة (Sanctum/ middleware auth)
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api;

use App\Models\Notifications;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->inAppNotifications()->latest();

        if ($request->has('is_read')) {
            $query->where('is_read', filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN));
        }

        $notifications = $query->paginate($request->integer('per_page', 20));

        return $this->successResponse(
            $notifications->items(),
            'Notifications retrieved.',
            200,
            $this->paginationMeta($notifications),
        );
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return $this->successResponse(
            [
                'unread_count' => $this->notifications->unreadCountFor($request->user()),
            ],
            'Unread count retrieved.',
        );
    }

    public function show(Request $request, Notifications $notification): JsonResponse
    {
        if (! $this->belongsToUser($request, $notification)) {
            return $this->notFoundResponse('Notification not found.');
        }

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return $this->successResponse(
            $notification->fresh(),
            'Notification retrieved.',
        );
    }

    public function markAsRead(Request $request, Notifications $notification): JsonResponse
    {
        if (! $this->belongsToUser($request, $notification)) {
            return $this->notFoundResponse('Notification not found.');
        }

        $notification->update(['is_read' => true]);

        return $this->successResponse(
            $notification->fresh(),
            'Notification marked as read.',
        );
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $updated = $request->user()
            ->inAppNotifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->successResponse(
            ['updated' => $updated],
            'All notifications marked as read.',
        );
    }

    public function destroy(Request $request, Notifications $notification): JsonResponse
    {
        if (! $this->belongsToUser($request, $notification)) {
            return $this->notFoundResponse('Notification not found.');
        }

        $notification->delete();

        return $this->deletedResponse('Notification deleted successfully.');
    }

    private function belongsToUser(Request $request, Notifications $notification): bool
    {
        return $notification->user_id === $request->user()->id;
    }
}
