<?php


use App\Models\Estate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('estate_videos', function (Blueprint $table) {
        $table->id();
        $table->foreignIdFor(Estate::class)->constrained()->onDelete('cascade');
        $table->string('video'); // أو text('video') إذا كانت الروابط طويلة جداً
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('estate_videos');
    }
};
