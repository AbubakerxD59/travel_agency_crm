<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folder_payments', function (Blueprint $table) {
            $table->timestamp('locked_at')->nullable()->after('approval_status');
        });

        DB::table('folder_payments')
            ->whereIn('approval_status', ['approved', 'rejected'])
            ->whereNull('locked_at')
            ->update(['locked_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('folder_payments', function (Blueprint $table) {
            $table->dropColumn('locked_at');
        });
    }
};
