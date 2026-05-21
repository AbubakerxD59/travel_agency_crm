<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table): void {
            $table->date('booking_date')->nullable()->after('travel_date');
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table): void {
            $table->dropColumn('booking_date');
        });
    }
};
