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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Companies::class)->constrained()->cascadeOnDelete();
            $table->string('profile_image')->nullable();
            $table->integer('views')->default(0);
            $table->integer('shares')->default(0);
            $table->unsignedTinyInteger('trust_score')->default(0);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
