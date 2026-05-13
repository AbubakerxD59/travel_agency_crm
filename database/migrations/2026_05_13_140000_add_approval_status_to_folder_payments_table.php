<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folder_payments', function (Blueprint $table): void {
            $table->string('approval_status', 20)->default('approved')->after('bank_id');
        });
    }

    public function down(): void
    {
        Schema::table('folder_payments', function (Blueprint $table): void {
            $table->dropColumn('approval_status');
        });
    }
};
