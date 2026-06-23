<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estate_images', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('is_primary');
        });

        Schema::table('estate_ads', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('is_main');
        });
    }

    public function down(): void
    {
        Schema::table('estate_images', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('estate_ads', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
