<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @return list<string>
     */
    private function tables(): array
    {
        return [
            'leads',
            'folders',
            'folder_itineraries',
            'folder_passengers',
            'folder_package_costs',
            'folder_hotel_details',
            'folder_transport_details',
            'folder_visa_details',
            'folder_other_details',
            'folder_payments',
        ];
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables() as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables() as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
