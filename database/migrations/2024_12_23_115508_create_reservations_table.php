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
        Schema::disableForeignKeyConstraints();

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->time('hour')->nullable(); // Cambia a tipo time
            $table->string('worker_dni', 20)->nullable();
            $table->foreign('worker_dni')->references('dni')->on('users');
            $table->string('client_dni', 20)->nullable();
            $table->foreign('client_dni')->references('dni')->on('clients');
            $table->unsignedBigInteger('service_id')->nullable(); // Asegúrate de que sea unsignedBigInteger
            $table->foreign('service_id')->references('id')->on('services');
            $table->unsignedBigInteger('shift_id')->nullable(); // Asegúrate de que sea unsignedBigInteger
            $table->foreign('shift_id')->references('id')->on('shifts');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
