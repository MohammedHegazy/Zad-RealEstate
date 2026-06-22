<?php
/*
|--------------------------------------------------------------------------
| NotificationService — إرسال إشعارات داخل التطبيق
|--------------------------------------------------------------------------
| يُستخدم من Controllers أو Jobs عند حدث (موافقة عقار، رسالة، ...).
| جدول: notifications (user_id, content, is_read).
|
| الفرق بين create و insert:
| - create: سجل واحد + events/observers
| - insert: دفعة كبيرة أسرع بدون Model events لكل صف
|--------------------------------------------------------------------------
*/

namespace App\Services;

use App\Models\Notifications;
use App\Models\User;

class NotificationService
{
    /**
     * إشعار لمستخدم واحد — يُرجع Model المُنشأ.
     */
    public function sendToUser(int $userId, string $content): Notifications
    {
        return Notifications::create([
            'user_id' => $userId,
            'content' => $content,
            'is_read' => false,
        ]);
    }

    /**
     * إشعار جماعي لعدة مستخدمين.
     * unique() يمنع تكرار نفس user_id في الدفعة.
     * يُرجع عدد الصفوف المُدخلة (0 إن المصفوفة فارغة).
     */
    public function sendToMany(array $userIds, string $content): int
    {
        $rows = collect($userIds)->unique()->map(fn (int $id) => [
            'user_id' => $id,
            'content' => $content,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all();

        if ($rows === []) {
            return 0;
        }

        Notifications::insert($rows);

        return count($rows);
    }

    /**
     * عدد الإشعارات غير المقروءة لمستخدم — للشارة (badge) في الواجهة.
     * inAppNotifications = علاقة على User
     */
    public function unreadCountFor(User $user): int
    {
        return $user->inAppNotifications()->where('is_read', false)->count();
    }
}
