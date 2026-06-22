<?php
/*
|--------------------------------------------------------------------------
| QueriesCities — Trait لبناء استعلامات المدن (Cities)
|--------------------------------------------------------------------------
|
| الغرض:
| - استعلام موحّد لقائمة المدن مع بحث بالاسم
|
| الارتباطات:
| - Model: App\Models\Cities
| - يُستخدم في: CityController
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Concerns;

use App\Models\Cities;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait QueriesCities
{
    /**
     * بناء استعلام المدن مع فلتر بحث اختياري.
     *
     * المدخلات:
     * - search: نص للبحث في حقل name
     *
     * المخرجات: Builder مرتّب أبجدياً باسم المدينة
     *
     * الخطوات:
     * 1) Cities::query()
     * 2) فلتر LIKE إن وُجد search
     * 3) orderBy('name')
     */
    protected function citiesQuery(Request $request): Builder
    {
        $query = Cities::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        return $query->orderBy('name');
    }
}
