<?php

use App\Models\Places;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Places::class)->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('website')->nullable();
            $table->integer('employees_num')->default(1);
            $table->text('description');
            $table->json('work_days');
            $table->string('status')->default('pending');
            $table->string('profile_image')->nullable();
            $table->string('banner_image')->nullable();
            $table->unsignedTinyInteger('trust_score')->default(0);
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['user_id', 'status']);
            $table->index('places_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
