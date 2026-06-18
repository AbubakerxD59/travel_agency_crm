<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abbreviations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('full_form', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abbreviations');
    }
};
