<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list(`{$table}`)");
            foreach ($indexes as $index) {
                if ($index->name === $indexName) {
                    return true;
                }
            }
            return false;
        }

        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Add performance indexes to frequently queried columns.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // work_date is filtered on almost every attendance query
            if (!$this->indexExists('attendances', 'attendances_work_date_index')) {
                $table->index('work_date');
            }
            // Composite index for user + date lookups
            if (!$this->indexExists('attendances', 'attendances_user_id_work_date_index')) {
                $table->index(['user_id', 'work_date']);
            }
        });

        Schema::table('salaries', function (Blueprint $table) {
            if (!$this->indexExists('salaries', 'salaries_user_id_month_year_index')) {
                $table->index(['user_id', 'month', 'year']);
            }
        });

        Schema::table('leaves', function (Blueprint $table) {
            if (!$this->indexExists('leaves', 'leaves_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
        });

        Schema::table('business_trips', function (Blueprint $table) {
            if (!$this->indexExists('business_trips', 'business_trips_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (!$this->indexExists('notifications', 'notifications_user_id_read_at_index')) {
                $table->index(['user_id', 'read_at']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if ($this->indexExists('attendances', 'attendances_work_date_index')) {
                $table->dropIndex(['work_date']);
            }
            if ($this->indexExists('attendances', 'attendances_user_id_work_date_index')) {
                $table->dropIndex(['user_id', 'work_date']);
            }
        });

        Schema::table('salaries', function (Blueprint $table) {
            if ($this->indexExists('salaries', 'salaries_user_id_month_year_index')) {
                $table->dropIndex(['user_id', 'month', 'year']);
            }
        });

        Schema::table('leaves', function (Blueprint $table) {
            if ($this->indexExists('leaves', 'leaves_user_id_status_index')) {
                $table->dropIndex(['user_id', 'status']);
            }
        });

        Schema::table('business_trips', function (Blueprint $table) {
            if ($this->indexExists('business_trips', 'business_trips_user_id_status_index')) {
                $table->dropIndex(['user_id', 'status']);
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if ($this->indexExists('notifications', 'notifications_user_id_read_at_index')) {
                $table->dropIndex(['user_id', 'read_at']);
            }
        });
    }
};
