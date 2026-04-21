<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('order_type');
            $table->string('vendor_reference')->nullable();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('status');
            $table->foreignId('destination_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->date('travel_date');
            $table->date('balance_due_date')->nullable();
            $table->boolean('ziarat_makkah')->default(false);
            $table->boolean('ziarat_madinah')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
