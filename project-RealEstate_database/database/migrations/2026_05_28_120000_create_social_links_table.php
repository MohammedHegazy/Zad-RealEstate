<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->morphs('socialable');//العقار الذي يتم إضافة الرابط إليه
            $table->string('platform');//المنصة الاجتماعية التي يتم إضافة الرابط إليها
            $table->string('url', 500);//الرابط الاجتماعي
            $table->timestamps();
            //تاريخ الإنشاء والتحديث
            $table->unique(['socialable_type', 'socialable_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
