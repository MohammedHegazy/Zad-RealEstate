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
        Schema::create('estates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Places::class)->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('name');
            $table->string('phone');
            $table->string('country_code_phone', 10);
            $table->decimal('space_of_estate', 10, 2);
            $table->decimal('price_of_meter', 12, 2);
            $table->decimal('price', 15, 2);
            $table->decimal('monthly_rent', 12, 2)->nullable();
            $table->decimal('annual_expenses', 12, 2)->default(0);
            $table->decimal('maintenance_cost', 12, 2)->default(0);
            $table->decimal('annual_property_tax', 12, 2)->default(0);
            $table->decimal('annual_hoa_or_service', 12, 2)->default(0);
            $table->decimal('occupancy_rate', 5, 2)->default(100);
            $table->decimal('expected_annual_income', 15, 2)->nullable();
            $table->decimal('roi', 10, 4)->nullable();
            $table->decimal('payback_period', 10, 2)->nullable();
            $table->integer('floor')->default(0);
            $table->integer('num_of_bedrooms')->default(0);
            $table->integer('num_of_livingrooms')->default(0);
            $table->integer('num_of_receptions')->default(0);
            $table->integer('num_of_bathrooms')->default(0);
            $table->integer('num_of_kitchens')->default(1);
            $table->integer('num_of_balconies')->default(0);
            $table->string('status')->default('pending');
            $table->string('type_text');
            $table->string('kind_text');
            $table->boolean('is_furnished')->default(false);
            $table->text('description');
            $table->string('real_number')->nullable();
            $table->string('date_of_build', 50)->nullable();
            $table->string('state_of_build')->nullable();
            $table->string('rent_kind')->nullable();
            $table->text('rent_description')->nullable();
            $table->integer('views')->default(0);
            $table->integer('shares')->default(0);
            $table->timestamps();

            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estates');
    }
};
