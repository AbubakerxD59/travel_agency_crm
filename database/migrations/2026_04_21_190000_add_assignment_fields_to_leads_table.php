<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('agent_id');
            $table->string('phone_number')->nullable()->after('customer_name');
            $table->string('email')->nullable()->after('phone_number');
            $table->string('city')->nullable()->after('email');
            $table->string('source')->nullable()->after('city');
            $table->text('notes')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'phone_number',
                'email',
                'city',
                'source',
                'notes',
            ]);
        });
    }
};
