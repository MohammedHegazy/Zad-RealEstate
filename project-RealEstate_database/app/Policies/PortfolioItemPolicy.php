<?php

namespace App\Policies;

use App\Models\PortfolioItem;
use App\Models\User;

class PortfolioItemPolicy
{
    public function viewAny(User $user): bool //عرض جميع العناصر المحفظة
    {
        return true; //يمكن لأي مستخدم العرض
    }

    public function view(User $user, PortfolioItem $portfolioItem): bool //عرض عنصر محفظة معين      
    {
        return $portfolioItem->portfolio?->user_id === $user->id; //يمكن للمستخدم العرض إذا كان العنصر محفظة معين هو المستخدم الذي قام بالتحفظ
    }

    public function create(User $user): bool //إنشاء عنصر محفظة
    {
        return true; //يمكن لأي مستخدم الإنشاء
    }

    public function update(User $user, PortfolioItem $portfolioItem): bool //تحديث عنصر محفظة
    {
        return $portfolioItem->portfolio?->user_id === $user->id; //يمكن للمستخدم التحديث إذا كان العنصر محفظة معين هو المستخدم الذي قام بالتحفظ
    }

    public function delete(User $user, PortfolioItem $portfolioItem): bool //حذف عنصر محفظة
    {
        return $portfolioItem->portfolio?->user_id === $user->id; //يمكن للمستخدم الحذف إذا كان العنصر محفظة معين هو المستخدم الذي قام بالتحفظ
    }
}
