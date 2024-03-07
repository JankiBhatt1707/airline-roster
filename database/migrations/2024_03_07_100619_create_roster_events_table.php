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
        Schema::create('roster_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roster_id');
            $table->foreign('roster_id')->references('id')->on('rosters')->onDelete('cascade');
            $table->string('flight_number');
            $table->string('type');
            $table->string('start_location');
            $table->string('end_location');
            $table->dateTime('date');
            $table->string('check_in_time');
            $table->string('check_out_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_events');
    }
};
