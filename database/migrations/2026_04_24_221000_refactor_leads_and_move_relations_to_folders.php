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
        Schema::create('folder_itineraries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('folder_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
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

        Schema::create('folder_passengers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('folder_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title', 20)->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('passenger_type', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('passport_details', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('folder_package_costs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('folder_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('ticket_no', 50)->nullable();
            $table->date('ticket_date')->nullable();
            $table->string('airline_from', 30)->nullable();
            $table->string('airline_to', 30)->nullable();
            $table->decimal('fare', 12, 2)->nullable();
            $table->decimal('tax', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->decimal('margin', 12, 2)->nullable();
            $table->decimal('sell', 12, 2)->nullable();
            $table->string('supplier', 100)->nullable();
            $table->string('pnr', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folder_itineraries');
        Schema::dropIfExists('folder_passengers');
        Schema::dropIfExists('folder_package_costs');
    }
};
