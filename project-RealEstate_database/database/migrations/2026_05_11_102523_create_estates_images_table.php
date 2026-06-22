<?php


use App\Models\Estate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estate_images', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Estate::class)->constrained()->onDelete('cascade');

            $table->string('image');

            $table->boolean('is_primary')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estate_images');
    }
};