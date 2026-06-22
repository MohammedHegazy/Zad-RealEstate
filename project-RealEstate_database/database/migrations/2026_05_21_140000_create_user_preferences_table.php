<?php

use App\Models\Cities;
use App\Models\Places;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->unique()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Cities::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Places::class)->nullable()->constrained()->nullOnDelete();
            $table->decimal('min_budget', 15, 2)->nullable();
            $table->decimal('max_budget', 15, 2)->nullable();
            $table->string('preferred_property_type')->nullable();
            $table->unsignedTinyInteger('preferred_rooms')->nullable();
            $table->string('property_function')->nullable();
            $table->string('investment_goal')->nullable();
            $table->string('risk_level')->default('moderate');
            $table->json('interests')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
