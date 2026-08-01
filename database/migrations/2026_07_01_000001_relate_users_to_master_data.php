<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add new foreign key columns
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('jabatan_id')->nullable()->constrained('master_data_values')->onDelete('set null');
            $table->foreignId('bagian_id')->nullable()->constrained('master_data_values')->onDelete('set null');
            $table->foreignId('status_pegawai_id')->nullable()->constrained('master_data_values')->onDelete('set null');
            $table->foreignId('status_operasional_id')->nullable()->constrained('master_data_values')->onDelete('set null');
        });

        // 2. Migrate existing string values to matching master_data_values IDs
        // We wrap it in a try-catch to prevent blocker during clean installation where tables might be empty
        try {
            $users = DB::table('users')->get();
            foreach ($users as $user) {
                $update = [];

                if (!empty($user->jabatan)) {
                    $value = DB::table('master_data_values')
                        ->join('master_data_types', 'master_data_values.master_data_type_id', '=', 'master_data_types.id')
                        ->where('master_data_types.slug', 'jabatan')
                        ->where('master_data_values.value', $user->jabatan)
                        ->select('master_data_values.id')
                        ->first();
                    if ($value) {
                        $update['jabatan_id'] = $value->id;
                    }
                }

                if (!empty($user->bagian)) {
                    $value = DB::table('master_data_values')
                        ->join('master_data_types', 'master_data_values.master_data_type_id', '=', 'master_data_types.id')
                        ->where('master_data_types.slug', 'bagian')
                        ->where('master_data_values.value', $user->bagian)
                        ->select('master_data_values.id')
                        ->first();
                    if ($value) {
                        $update['bagian_id'] = $value->id;
                    }
                }

                if (!empty($user->status_pegawai)) {
                    $value = DB::table('master_data_values')
                        ->join('master_data_types', 'master_data_values.master_data_type_id', '=', 'master_data_types.id')
                        ->where('master_data_types.slug', 'status-pegawai')
                        ->where('master_data_values.value', $user->status_pegawai)
                        ->select('master_data_values.id')
                        ->first();
                    if ($value) {
                        $update['status_pegawai_id'] = $value->id;
                    }
                }

                if (!empty($user->status_operasional)) {
                    $value = DB::table('master_data_values')
                        ->join('master_data_types', 'master_data_values.master_data_type_id', '=', 'master_data_types.id')
                        ->where('master_data_types.slug', 'status_oprasional')
                        ->where('master_data_values.value', $user->status_operasional)
                        ->select('master_data_values.id')
                        ->first();
                    if ($value) {
                        $update['status_operasional_id'] = $value->id;
                    }
                }

                if (!empty($update)) {
                    DB::table('users')->where('id', $user->id)->update($update);
                }
            }
        } catch (\Exception $e) {
            // Ignore seeder exceptions if the master data types don't exist yet
        }

        // 3. Drop old string columns
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'jabatan')) {
                $table->dropColumn('jabatan');
            }
        });
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'bagian')) {
                $table->dropColumn('bagian');
            }
        });
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status_pegawai')) {
                $table->dropColumn('status_pegawai');
            }
        });
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status_operasional')) {
                $table->dropColumn('status_operasional');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add old string columns
        Schema::table('users', function (Blueprint $table) {
            $table->string('jabatan')->nullable();
            $table->string('bagian')->nullable();
            $table->string('status_pegawai')->nullable();
            $table->string('status_operasional')->nullable();
        });

        // 2. Map IDs back to string values
        try {
            $users = DB::table('users')->get();
            foreach ($users as $user) {
                $update = [];

                if ($user->jabatan_id) {
                    $val = DB::table('master_data_values')->where('id', $user->jabatan_id)->value('value');
                    if ($val) $update['jabatan'] = $val;
                }

                if ($user->bagian_id) {
                    $val = DB::table('master_data_values')->where('id', $user->bagian_id)->value('value');
                    if ($val) $update['bagian'] = $val;
                }

                if ($user->status_pegawai_id) {
                    $val = DB::table('master_data_values')->where('id', $user->status_pegawai_id)->value('value');
                    if ($val) $update['status_pegawai'] = $val;
                }

                if ($user->status_operasional_id) {
                    $val = DB::table('master_data_values')->where('id', $user->status_operasional_id)->value('value');
                    if ($val) $update['status_operasional'] = $val;
                }

                if (!empty($update)) {
                    DB::table('users')->where('id', $user->id)->update($update);
                }
            }
        } catch (\Exception $e) {
            // Ignore rollback mapping errors
        }

        // 3. Drop Foreign Keys and Columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
            $table->dropForeign(['bagian_id']);
            $table->dropForeign(['status_pegawai_id']);
            $table->dropForeign(['status_operasional_id']);

            $table->dropColumn(['jabatan_id', 'bagian_id', 'status_pegawai_id', 'status_operasional_id']);
        });
    }
};
