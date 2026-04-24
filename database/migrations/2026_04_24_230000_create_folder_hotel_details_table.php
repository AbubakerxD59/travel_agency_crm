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
        Schema::create('folder_hotel_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('folder_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('sr_no')->nullable();
            $table->string('supplier', 100)->nullable();
            $table->string('hotel_name', 150)->nullable();
            $table->string('guest_name', 150)->nullable();
            $table->unsignedInteger('rooms')->nullable();
            $table->string('type', 100)->nullable();
            $table->string('meals', 100)->nullable();
            $table->date('date_in')->nullable();
            $table->date('date_out')->nullable();
            $table->unsignedInteger('nights')->nullable();
            $table->string('supplier_ref', 100)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->decimal('margin', 12, 2)->nullable();
            $table->decimal('sell', 12, 2)->nullable();
            $table->string('hotel_city', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folder_hotel_details');
    }
};
