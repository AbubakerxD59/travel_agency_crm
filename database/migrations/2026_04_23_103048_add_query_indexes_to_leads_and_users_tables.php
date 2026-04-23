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
        Schema::table('leads', function (Blueprint $table) {
            $table->index('status');
            $table->index('source');
            $table->index('travel_date');
            $table->index(['agent_id', 'travel_date', 'id'], 'leads_agent_travel_id_idx');
            $table->index(['company_id', 'status'], 'leads_company_status_idx');
            $table->index('customer_name');
            $table->index('phone_number');
            $table->index('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('name');
            $table->index('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['source']);
            $table->dropIndex(['travel_date']);
            $table->dropIndex('leads_agent_travel_id_idx');
            $table->dropIndex('leads_company_status_idx');
            $table->dropIndex(['customer_name']);
            $table->dropIndex(['phone_number']);
            $table->dropIndex(['email']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['phone_number']);
        });
    }
};
