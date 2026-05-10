<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folder_transport_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('folder_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('supplier', 100);
            $table->string('description', 255)->nullable();
            $table->string('origin', 150)->nullable();
            $table->string('destination', 150)->nullable();
            $table->date('service_date')->nullable();
            $table->string('pickup_time', 30)->nullable();
            $table->string('vehicle_type', 100)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->decimal('margin', 12, 2)->nullable();
            $table->decimal('sell', 12, 2)->nullable();
            $table->decimal('sar', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folder_transport_details');
    }
};
