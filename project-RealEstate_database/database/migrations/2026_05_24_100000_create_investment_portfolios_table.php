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
        Schema::create('investment_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('target_budget', 15, 2)->nullable();
            $table->string('risk_level')->default('moderate');
            $table->string('status')->default('active');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });

        Schema::create('portfolio_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('investment_portfolios')->cascadeOnDelete();
            $table->foreignIdFor(Estate::class)->constrained()->cascadeOnDelete();
            $table->string('status')->default('tracking');
            $table->decimal('investment_amount', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('invested_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->unique(['portfolio_id', 'estate_id']);
            $table->index(['portfolio_id', 'status']);
            $table->index(['estate_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_properties');
        Schema::dropIfExists('investment_portfolios');
    }
};
