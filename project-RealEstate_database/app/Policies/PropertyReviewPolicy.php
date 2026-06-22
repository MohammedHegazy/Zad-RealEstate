<?php

namespace App\Policies;

use App\Models\PropertyReview;
use App\Models\User;

class PropertyReviewPolicy
{
    public function viewAny(?User $user): bool //عرض جميع التقييمات
    {
        return true; //يمكن لأي مستخدم العرض
    }

    public function view(?User $user, PropertyReview $review): bool //عرض تقييم معين
    {
        return $review->isApproved() || ($user !== null && $user->id === $review->user_id); //يمكن للمستخدم العرض إذا كان التقييم موافق عليه أو إذا كان المستخدم هو المستخدم الذي قام بالتقييم
    }

    public function create(User $user): bool //إنشاء تقييم
    {
        return true; //يمكن لأي مستخدم الإنشاء
    }

    public function update(User $user, PropertyReview $review): bool //تحديث تقييم
    {
        return $user->id === $review->user_id; //يمكن للمستخدم التحديث إذا كان المستخدم هو المستخدم الذي قام بالتقييم
    }

    public function delete(User $user, PropertyReview $review): bool //حذف تقييم
    {
        return $user->id === $review->user_id; //يمكن للمستخدم الحذف إذا كان المستخدم هو المستخدم الذي قام بالتقييم
    }

    public function moderate(User $user): bool //مراجعة تقييم
    {
        return $user->isAdmin(); //يمكن للمدير المراجعة 
    }
}
