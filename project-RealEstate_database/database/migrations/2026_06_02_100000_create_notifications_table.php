<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();//المستخدم الذي قام بالإشعار    
            $table->text('content');//المحتوى الذي يتم إرساله
            $table->boolean('is_read')->default(false);//هل الإشعار قراءة أم لا
            $table->timestamps();//تاريخ الإنشاء والتحديث
            $table->index(['user_id', 'is_read']);//ايجاد الإشعارات التي لم يقرؤها المستخدم بحسب الحالة
            $table->index(['user_id', 'created_at']);//ايجاد الإشعارات التي يتم إرسالها بحسب التاريخ
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
