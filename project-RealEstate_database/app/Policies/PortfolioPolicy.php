<?php

namespace App\Policies;

use App\Models\InvestmentPortfolio;
use App\Models\Portfolio;
use App\Models\User;

class PortfolioPolicy
{
    /**
     * Any authenticated user may list their own portfolios.
     */
    public function viewAny(User $user): bool //عرض جميع المحفظات
    {
        return true; //يمكن لأي مستخدم العرض
    }

    public function view(User $user, Portfolio|InvestmentPortfolio $portfolio): bool //عرض محفظة معين
    {
        return $portfolio->user_id === $user->id; //يمكن للمستخدم العرض إذا كان المحفظة معين هو المستخدم الذي قام بالتحفظ
    }

    public function create(User $user): bool //إنشاء محفظة
    {
        return true; //يمكن لأي مستخدم الإنشاء
    }

    public function update(User $user, Portfolio|InvestmentPortfolio $portfolio): bool //تحديث محفظة
    {
        return $portfolio->user_id === $user->id; //يمكن للمستخدم التحديث إذا كان المحفظة معين هو المستخدم الذي قام بالتحفظ
    }

    public function delete(User $user, Portfolio|InvestmentPortfolio $portfolio): bool //حذف محفظة
    {
        return $portfolio->user_id === $user->id; //يمكن للمستخدم الحذف إذا كان المحفظة معين هو المستخدم الذي قام بالتحفظ
    }
}
