<?php

use App\Models\Estate;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();//المستخدم الذي ستظهر له التوصية
            $table->foreignIdFor(Estate::class)->constrained()->cascadeOnDelete();//العقار المقترح
            $table->decimal('recommendation_score', 5, 2);//درجة التوصية 
            $table->decimal('matching_percentage', 5, 2);// %نسبة التطابق مع تفضيلات المستخدم  80
            $table->json('score_factors');//جودة التوصيات النهائية  لماذا اقترحت هذا العقار 
            $table->json('recommendation_reason');//الاسباب التي ستظهر في التوصية  يناسب الميزانية ضمن مدينتك المضلة 
            $table->boolean('is_active')->default(true);//التوصية مفعلة ام غير مفعلة 
            $table->timestamps();

            $table->unique(['user_id', 'estate_id']);//
            $table->index(['user_id', 'is_active', 'recommendation_score']);//
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
