<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folder_payments', function (Blueprint $table): void {
            $table->string('reference_no', 100)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('folder_payments', function (Blueprint $table): void {
            $table->dropColumn('reference_no');
        });
    }
};
