<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('agent_cnic', 32)->nullable()->after('phone_number');
            $table->text('home_address')->nullable()->after('agent_cnic');
            $table->string('guardian_name')->nullable()->after('home_address');
            $table->string('guardian_phone_number', 32)->nullable()->after('guardian_name');
            $table->string('guardian_cnic', 32)->nullable()->after('guardian_phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'agent_cnic',
                'home_address',
                'guardian_name',
                'guardian_phone_number',
                'guardian_cnic',
            ]);
        });
    }
};
