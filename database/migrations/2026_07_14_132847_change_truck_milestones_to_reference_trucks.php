<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('truck_milestones', 'booking_truck_id')) {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'truck_milestones'
                AND COLUMN_NAME = 'booking_truck_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE truck_milestones DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }

            // Drop the old unique index if it exists, under any name
            $indexes = DB::select("SHOW INDEX FROM truck_milestones WHERE Column_name = 'booking_truck_id'");
            foreach ($indexes as $index) {
                if ($index->Key_name !== 'PRIMARY') {
                    DB::statement("ALTER TABLE truck_milestones DROP INDEX `{$index->Key_name}`");
                }
            }

            Schema::table('truck_milestones', function (Blueprint $table) {
                $table->dropColumn('booking_truck_id');
            });
        }

        if (!Schema::hasColumn('truck_milestones', 'truck_id')) {
            Schema::table('truck_milestones', function (Blueprint $table) {
                $table->foreignId('truck_id')->after('id')->constrained()->cascadeOnDelete();
            });
        }

        $indexExists = DB::select("SHOW INDEX FROM truck_milestones WHERE Key_name = 'truck_milestones_truck_id_checkpoint_id_unique'");
        if (empty($indexExists)) {
            Schema::table('truck_milestones', function (Blueprint $table) {
                $table->unique(['truck_id', 'checkpoint_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('truck_milestones', function (Blueprint $table) {
            if (Schema::hasColumn('truck_milestones', 'truck_id')) {
                $table->dropForeign(['truck_id']);
                $table->dropUnique(['truck_id', 'checkpoint_id']);
                $table->dropColumn('truck_id');
            }
        });

        Schema::table('truck_milestones', function (Blueprint $table) {
            if (!Schema::hasColumn('truck_milestones', 'booking_truck_id')) {
                $table->foreignId('booking_truck_id')->constrained();
                $table->unique(['booking_truck_id', 'checkpoint_id']);
            }
        });
    }
};