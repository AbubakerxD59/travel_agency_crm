<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('sr_no')->nullable();
            $table->string('airline_code', 20)->nullable();
            $table->string('airline_number', 30)->nullable();
            $table->string('class', 20)->nullable();
            $table->date('departure_date')->nullable();
            $table->string('departure_airport', 30)->nullable();
            $table->string('arrival_airport', 30)->nullable();
            $table->time('departure_time')->nullable();
            $table->time('arrival_time')->nullable();
            $table->date('arrival_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_itineraries');
    }
};
