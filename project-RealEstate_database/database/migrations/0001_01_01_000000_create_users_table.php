<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('fname');
            $table->string('lname');
            $table->string('status')->default('active');
            $table->string('type');
            $table->boolean('is_verified')->default(false);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('countre_code_phone');
            $table->string('gender');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
