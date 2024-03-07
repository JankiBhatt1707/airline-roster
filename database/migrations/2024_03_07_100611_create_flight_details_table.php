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
        Schema::create('flight_details', function (Blueprint $table) {
            $table->id();
            $table->string('flight_number')->nullable();
            $table->string('scheduled_time_departure')->nullable();
            $table->string('scheduled_time_arrival')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_details');
    }
};
