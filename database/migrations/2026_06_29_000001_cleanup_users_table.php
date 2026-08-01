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
     * Check if a column exists on a table.
     */
    private function columnExists(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    /**
     * Cleanup tabel users:
     * 1. Hapus kolom 'employee_type' yang tidak digunakan (redundan dengan kolom 'jabatan').
     * 2. Tambahkan index pada kolom 'nip' dan 'nik' untuk mempercepat pencarian & login.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Hapus kolom employee_type jika masih ada
            if ($this->columnExists('users', 'employee_type')) {
                $table->dropColumn('employee_type');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // 2. Tambahkan index pada nip untuk mempercepat pencarian data pegawai
            if (!$this->indexExists('users', 'users_nip_index')) {
                $table->index('nip', 'users_nip_index');
            }

            // 3. Tambahkan index pada nik untuk mempercepat pencarian data pegawai
            if (!$this->indexExists('users', 'users_nik_index')) {
                $table->index('nik', 'users_nik_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan index yang dihapus
            if ($this->indexExists('users', 'users_nip_index')) {
                $table->dropIndex('users_nip_index');
            }

            if ($this->indexExists('users', 'users_nik_index')) {
                $table->dropIndex('users_nik_index');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Kembalikan kolom employee_type
            if (!$this->columnExists('users', 'employee_type')) {
                $table->string('employee_type')->nullable()->after('attendance_type');
            }
        });
    }
};
