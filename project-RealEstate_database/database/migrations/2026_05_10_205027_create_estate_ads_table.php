<?php


use App\Models\Estate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
       Schema::create('estate_ads', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Estate::class)->constrained()->onDelete('cascade');
            $table->string('image'); 
            $table->boolean('is_main')->default(false); //الصورة الرئيسة للعقارات
            $table->timestamps();
});

    }

    
    public function down(): void
    {
        Schema::dropIfExists('estate_ads');
    }
};
