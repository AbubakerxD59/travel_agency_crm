<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folder_other_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('folder_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('supplier', 100);
            $table->string('description', 255)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->decimal('margin', 12, 2)->nullable();
            $table->decimal('sell', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folder_other_details');
    }
};
