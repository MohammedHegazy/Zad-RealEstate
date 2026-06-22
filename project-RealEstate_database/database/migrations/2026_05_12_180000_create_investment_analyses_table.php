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
        Schema::create('investment_analyses', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();//المستخدم الذي قام بالتحليل
            $table->foreignIdFor(Estate::class)->constrained()->cascadeOnDelete();//العقار الذي تم تحليله 

            $table->decimal('property_price', 15, 2);//سعر العقار وقت التحليل
            $table->decimal('monthly_rent', 12, 2)->default(0);//الايجار الشهري 
            $table->decimal('annual_expenses', 12, 2)->default(0);  //المصاريف السنوية
            $table->decimal('maintenance_cost', 12, 2)->default(0);// تكاليف الصيانة السنوية
            $table->decimal('tax_cost', 12, 2)->default(0);//ضريبة العقار السنوية

            $table->decimal('occupancy_rate', 5, 2)->default(100);//نسبة اشغال العقار مثال العقار مؤجر طوال السنة

            $table->decimal('expected_annual_income', 15, 2)->nullable();//الدخل السنوي المتوقع
            $table->decimal('roi', 10, 4)->nullable();//عائد الاسثمار السنوي
            $table->decimal('payback_period', 10, 2)->nullable();//فترة الاسترداد

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_analyses');
    }
};
