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
        Schema::disableForeignKeyConstraints();

        // 1. Create new master_data table
        Schema::create('master_data', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('value');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Migrate existing data from master_data_values to master_data
        if (Schema::hasTable('master_data_values')) {
            $oldValues = DB::table('master_data_values')->get();
            
            // Map type slugs
            $typeMapping = [];
            if (Schema::hasTable('master_data_types')) {
                $types = DB::table('master_data_types')->get();
                foreach ($types as $type) {
                    $enumVal = $type->slug;
                    if ($enumVal === 'status-pegawai') {
                        $enumVal = 'status_pegawai';
                    } elseif ($enumVal === 'status-operasional') {
                        $enumVal = 'status_operasional';
                    }
                    $typeMapping[$type->id] = $enumVal;
                }
            }

            foreach ($oldValues as $val) {
                DB::table('master_data')->insert([
                    'id' => $val->id,
                    'type' => $typeMapping[$val->master_data_type_id] ?? 'jabatan',
                    'value' => $val->value,
                    'description' => $val->description,
                    'is_active' => $val->is_active ?? true,
                    'sort_order' => $val->sort_order ?? 0,
                    'created_at' => $val->created_at,
                    'updated_at' => $val->updated_at,
                ]);
            }
        }

        // 3. Recreate employee_profiles to update foreign key references from master_data_values to master_data
        if (Schema::hasTable('employee_profiles')) {
            $oldProfiles = DB::table('employee_profiles')->get();

            Schema::dropIfExists('employee_profiles');

            Schema::create('employee_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('nik', 16)->unique();
                $table->decimal('gaji_pokok', 15, 2)->default(0);
                $table->text('alamat')->nullable();
                $table->string('no_telepon', 20)->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
                $table->foreignId('jabatan_id')->nullable()->constrained('master_data')->nullOnDelete();
                $table->foreignId('bagian_id')->nullable()->constrained('master_data')->nullOnDelete();
                $table->foreignId('status_pegawai_id')->nullable()->constrained('master_data')->nullOnDelete();
                $table->foreignId('status_operasional_id')->nullable()->constrained('master_data')->nullOnDelete();
                $table->enum('attendance_type', ['normal', 'shift', 'umum'])->default('normal');
                $table->timestamps();
            });

            foreach ($oldProfiles as $p) {
                DB::table('employee_profiles')->insert((array)$p);
            }
        }

        // 4. Drop old tables
        Schema::dropIfExists('master_data_values');
        Schema::dropIfExists('master_data_types');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Recreate master_data_types table
        Schema::create('master_data_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('scope')->default('admin');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default types
        $types = [
            ['name' => 'Jabatan', 'slug' => 'jabatan', 'scope' => 'psdm'],
            ['name' => 'Bagian', 'slug' => 'bagian', 'scope' => 'psdm'],
            ['name' => 'Status Pegawai', 'slug' => 'status-pegawai', 'scope' => 'psdm'],
            ['name' => 'Status Operasional', 'slug' => 'status-operasional', 'scope' => 'psdm'],
        ];
        foreach ($types as $t) {
            DB::table('master_data_types')->insert(array_merge($t, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 2. Recreate master_data_values table
        Schema::create('master_data_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_data_type_id')->nullable();
            $table->string('value');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed values back to master_data_values
        if (Schema::hasTable('master_data')) {
            $values = DB::table('master_data')->get();
            $dbTypes = DB::table('master_data_types')->get()->keyBy('slug');

            foreach ($values as $val) {
                $slug = $val->type;
                if ($slug === 'status_pegawai') {
                    $slug = 'status-pegawai';
                } elseif ($slug === 'status_operasional') {
                    $slug = 'status-operasional';
                }
                
                $typeId = $dbTypes->get($slug)?->id;

                DB::table('master_data_values')->insert([
                    'id' => $val->id,
                    'master_data_type_id' => $typeId,
                    'value' => $val->value,
                    'description' => $val->description,
                    'is_active' => $val->is_active,
                    'sort_order' => $val->sort_order,
                    'created_at' => $val->created_at,
                    'updated_at' => $val->updated_at,
                ]);
            }
        }

        // 3. Update employee_profiles to point to master_data_values
        if (Schema::hasTable('employee_profiles')) {
            $oldProfiles = DB::table('employee_profiles')->get();

            Schema::dropIfExists('employee_profiles');

            Schema::create('employee_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('nik', 16)->unique();
                $table->decimal('gaji_pokok', 15, 2)->default(0);
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

            foreach ($oldProfiles as $p) {
                DB::table('employee_profiles')->insert((array)$p);
            }
        }

        // 4. Drop master_data table
        Schema::dropIfExists('master_data');

        Schema::enableForeignKeyConstraints();
    }
};
