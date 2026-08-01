<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Step 1: Buat tabel employee_profiles
     * Step 2: Migrasi data dari users ke employee_profiles (hanya role=user)
     * Step 3: Hapus kolom kepegawaian dari tabel users (NIP TETAP di users)
     */
    public function up(): void
    {
        // ===== STEP 1: Buat tabel employee_profiles =====
        // NIP TIDAK dipindahkan — tetap di tabel users karena dipakai semua role
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nik', 16)->nullable()->index();
            $table->decimal('gaji_pokok', 15, 2)->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_telepon', 20)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->foreignId('jabatan_id')->nullable()->constrained('master_data_values')->nullOnDelete();
            $table->foreignId('bagian_id')->nullable()->constrained('master_data_values')->nullOnDelete();
            $table->foreignId('status_pegawai_id')->nullable()->constrained('master_data_values')->nullOnDelete();
            $table->foreignId('status_operasional_id')->nullable()->constrained('master_data_values')->nullOnDelete();
            $table->enum('attendance_type', ['normal', 'shift', 'umum'])->default('normal');
            $table->timestamps();
        });

        // ===== STEP 2: Migrasi data dari users ke employee_profiles =====
        try {
            $users = DB::table('users')->where('role', 'user')->get();

            foreach ($users as $user) {
                DB::table('employee_profiles')->insert([
                    'user_id' => $user->id,
                    'nik' => $user->nik ?? null,
                    'gaji_pokok' => $user->gaji_pokok ?? null,
                    'alamat' => $user->alamat ?? null,
                    'no_telepon' => $user->no_telepon ?? null,
                    'tanggal_lahir' => $user->tanggal_lahir ?? null,
                    'jenis_kelamin' => $user->jenis_kelamin ?? null,
                    'jabatan_id' => $user->jabatan_id ?? null,
                    'bagian_id' => $user->bagian_id ?? null,
                    'status_pegawai_id' => $user->status_pegawai_id ?? null,
                    'status_operasional_id' => $user->status_operasional_id ?? null,
                    'attendance_type' => $user->attendance_type ?? 'normal',
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
        } catch (\Exception $e) {
            // Pada fresh install / testing, tabel users mungkin kosong
        }

        // ===== STEP 3: Hapus kolom kepegawaian dari tabel users =====
        // NIP TETAP di users — hanya hapus kolom biodata/employment
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign keys dulu sebelum drop kolom
            try { $table->dropForeign(['jabatan_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['bagian_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['status_pegawai_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['status_operasional_id']); } catch (\Exception $e) {}
        });

        Schema::table('users', function (Blueprint $table) {
            try { $table->dropIndex(['nik']); } catch (\Exception $e) {}
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nik', 'gaji_pokok', 'alamat', 'no_telepon',
                'tanggal_lahir', 'jenis_kelamin',
                'jabatan_id', 'bagian_id', 'status_pegawai_id', 'status_operasional_id',
                'attendance_type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan kolom ke tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->index()->after('nip');
            $table->decimal('gaji_pokok', 15, 2)->nullable()->after('nik');
            $table->text('alamat')->nullable()->after('gaji_pokok');
            $table->string('no_telepon', 20)->nullable()->after('alamat');
            $table->date('tanggal_lahir')->nullable()->after('no_telepon');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('tanggal_lahir');
            $table->foreignId('jabatan_id')->nullable()->constrained('master_data_values')->nullOnDelete()->after('jenis_kelamin');
            $table->foreignId('bagian_id')->nullable()->constrained('master_data_values')->nullOnDelete()->after('jabatan_id');
            $table->foreignId('status_pegawai_id')->nullable()->constrained('master_data_values')->nullOnDelete()->after('bagian_id');
            $table->foreignId('status_operasional_id')->nullable()->constrained('master_data_values')->nullOnDelete()->after('status_pegawai_id');
            $table->enum('attendance_type', ['normal', 'shift', 'umum'])->default('normal')->after('role');
        });

        // Kembalikan data dari employee_profiles ke users
        try {
            $profiles = DB::table('employee_profiles')->get();
            foreach ($profiles as $profile) {
                DB::table('users')->where('id', $profile->user_id)->update([
                    'nik' => $profile->nik,
                    'gaji_pokok' => $profile->gaji_pokok,
                    'alamat' => $profile->alamat,
                    'no_telepon' => $profile->no_telepon,
                    'tanggal_lahir' => $profile->tanggal_lahir,
                    'jenis_kelamin' => $profile->jenis_kelamin,
                    'jabatan_id' => $profile->jabatan_id,
                    'bagian_id' => $profile->bagian_id,
                    'status_pegawai_id' => $profile->status_pegawai_id,
                    'status_operasional_id' => $profile->status_operasional_id,
                    'attendance_type' => $profile->attendance_type,
                ]);
            }
        } catch (\Exception $e) {}

        Schema::dropIfExists('employee_profiles');
    }
};
