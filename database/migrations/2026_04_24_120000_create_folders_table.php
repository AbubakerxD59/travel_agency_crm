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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_type');
            $table->string('vendor_reference')->nullable();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->date('travel_date');
            $table->date('balance_due_date')->nullable();
            $table->boolean('makkah_ziarat')->default(false);
            $table->boolean('madinah_ziarat')->default(false);
            $table->string('status')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'travel_date']);
            $table->index('company_id');
            $table->index('destination_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
