<?php

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();//المستخدم الذي قام بالتقييم
            $table->foreignIdFor(Agent::class)->constrained()->cascadeOnDelete();//الوكيل الذي تم التقييم عليه
            $table->unsignedTinyInteger('rating');//درجة التقييم من 1 الى 5
            $table->text('review')->nullable();//التقييم الذي قام به المستخدم
            $table->string('status', 20)->default('pending');
            //pending → معلق
            //approved → موافق عليه
            //rejected → مرفوض
            $table->timestamps();//تاريخ الإنشاء والتحديث

            $table->unique(['user_id', 'agent_id']);//لا يمكن للمستخدم التقييم مرتين لنفس الوكيل
            $table->index(['agent_id', 'status']);//ايجاد الوكلاء التي تم التقييم عليها بحسب الحالة
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_reviews');
    }
};
