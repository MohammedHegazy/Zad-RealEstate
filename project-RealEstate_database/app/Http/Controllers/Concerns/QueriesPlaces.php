<?php
/*
|--------------------------------------------------------------------------
| QueriesPlaces — Trait لبناء استعلامات المناطق (Places)
|--------------------------------------------------------------------------
|
| الغرض:
| - تجميع منطق الفلترة والترتيب لجدول places في مكان واحد
| - يُعاد استخدامه من PlaceController (وأي كنترولر آخر يحتاج نفس الاستعلام)
|
| الارتباطات:
| - Model: App\Models\Places
| - يُستخدم في: PlaceController عبر use QueriesPlaces
|
| لماذا Trait وليس Service؟
| - الاستعلام بسيط ومربوط مباشرة بـ Request؛ Trait يقلل التكرار دون طبقة إضافية
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Concerns;

use App\Models\Places;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait QueriesPlaces
{
    /**
     * بناء استعلام Eloquent جاهز لقائمة المناطق مع فلاتر اختيارية.
     *
     * المدخلات (من $request):
     * - cities_id: تصفية مناطق مدينة معيّنة
     * - search: بحث جزئي في اسم المنطقة (LIKE)
     *
     * المخرجات:
     * - Builder (لم يُنفَّذ بعد) — يُكمَّل بـ paginate() في الكنترولر
     *
     * الخطوات:
     * 1) بدء استعلام Places مع تحميل علاقة city (حقول محدودة للأداء)
     * 2) إن وُجد cities_id → where على عمود المدينة
     * 3) إن وُجد search → where name LIKE
     * 4) ترتيب أبجدي حسب الاسم
     */
    protected function placesQuery(Request $request): Builder
    {
        // with('city') = Eager Loading لتجنب مشكلة N+1 عند عرض المدينة مع كل منطقة
        $query = Places::query()->with('city:id,name,image');

        if ($request->filled('cities_id')) {
            $query->where('cities_id', $request->cities_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        return $query->orderBy('name');
    }
}
