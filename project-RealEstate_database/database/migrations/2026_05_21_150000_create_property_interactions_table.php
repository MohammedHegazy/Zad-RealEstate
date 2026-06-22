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
        Schema::create('property_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();//المستخدم الذي قام بالتفاعل
            $table->foreignIdFor(Estate::class)->constrained()->cascadeOnDelete();//العقار الذي تم التفاعل عليه
            $table->string('interaction_type');//نوع التفاعل مثل المشاهدة اضافة الى المفضلة التواصل مع الوكيل و الاشتراك في العقار 
            $table->unsignedSmallInteger('interaction_score');//درجة التفاعل مثل 1 2 3 
            $table->timestamps();

            $table->index(['user_id', 'created_at']);//اعطني اخر تفاعلات المستخدم 
            $table->index(['user_id', 'estate_id']);//هل تفاعل المستخدم مع هذا العقار 
            $table->index(['estate_id', 'interaction_type']);//كم عدد المفضلات لهذا العقار 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_interactions');
    }
};
