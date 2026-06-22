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
        Schema::create('property_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();//المستخدم الذي قام بالتقييم
            $table->foreignIdFor(Estate::class)->constrained()->cascadeOnDelete();//العقار الذي تم التقييم عليه
            $table->unsignedTinyInteger('rating');//درجة التقييم من 1 الى 5
            $table->text('review')->nullable();//التقييم الذي قام به المستخدم
            $table->string('status', 20)->default('pending');
            $table->timestamps();//تاريخ الإنشاء والتحديث

            $table->unique(['user_id', 'estate_id']);//لا يمكن للمستخدم التقييم مرتين لنفس العقار
            $table->index(['estate_id', 'status']);//ايجاد العقارات التي تم التقييم عليها بحسب الحالة
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_reviews');
    }
};
