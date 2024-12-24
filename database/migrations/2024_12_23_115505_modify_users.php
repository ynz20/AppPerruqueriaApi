<?php

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
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->string('DNI', 20)->unique()->nullable();
            $table->string('Name', 100)->nullable();
            $table->string('Surname', 100)->nullable();
            $table->string('Telf', 20)->nullable();
            $table->string('Email', 100)->nullable();
            $table->timestamp('Email_verified_at')->nullable();
            $table->string('Nick', 50)->nullable();
            $table->string('Password', 255)->nullable();
            $table->boolean('Is_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};