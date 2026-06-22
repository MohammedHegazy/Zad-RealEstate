<?php

use App\Models\Companies;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();//المستخدم الذي قام بالتقييم
            $table->foreignIdFor(Companies::class, 'company_id')->constrained('companies')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');//درجة التقييم من 1 الى 5
            $table->text('review')->nullable();//التقييم الذي قام به المستخدم
            $table->string('status', 20)->default('pending');
            //حالة التقييم pending → معلق
            //pending → معلق
            //approved → موافق عليه
            //rejected → مرفوض
            $table->timestamps();//تاريخ الإنشاء والتحديث
            $table->unique(['user_id', 'company_id']);//لا يمكن للمستخدم التقييم مرتين لنفس الشركة
            $table->index(['company_id', 'status']);//ايجاد الشركات التي تم التقييم عليها بحسب الحالة
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_reviews');
    }
};
