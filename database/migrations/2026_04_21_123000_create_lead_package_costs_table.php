<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_package_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
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

    public function down(): void
    {
        Schema::dropIfExists('lead_package_costs');
    }
};
