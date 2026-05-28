<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table): void {
            $table->string('agent_name')->nullable()->after('agent_id');
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->string('agent_name')->nullable()->after('agent_id');
        });

        DB::table('folders')
            ->whereNotNull('agent_id')
            ->orderBy('id')
            ->chunkById(200, function ($folders): void {
                foreach ($folders as $folder) {
                    $name = DB::table('users')->where('id', $folder->agent_id)->value('name');
                    if ($name !== null && $name !== '') {
                        DB::table('folders')->where('id', $folder->id)->update(['agent_name' => $name]);
                    }
                }
            });

        DB::table('leads')
            ->whereNotNull('agent_id')
            ->orderBy('id')
            ->chunkById(200, function ($leads): void {
                foreach ($leads as $lead) {
                    $name = DB::table('users')->where('id', $lead->agent_id)->value('name');
                    if ($name !== null && $name !== '') {
                        DB::table('leads')->where('id', $lead->id)->update(['agent_name' => $name]);
                    }
                }
            });

        Schema::table('folders', function (Blueprint $table): void {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('folders', function (Blueprint $table): void {
            $table->foreign('agent_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table): void {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('folders', function (Blueprint $table): void {
            $table->foreign('agent_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::table('folders', function (Blueprint $table): void {
            $table->dropColumn('agent_name');
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropColumn('agent_name');
        });
    }
};
