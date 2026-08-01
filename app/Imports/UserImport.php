<?php

namespace App\Imports;

use App\Models\User;
use App\Models\EmployeeProfile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;

class UserImport implements ToCollection, WithCalculatedFormulas
{
    protected $results = [];
    protected $errors = [];
    protected $skipped = [];

    public function collection(Collection $rows)
    {
        // Step 1: Find the header row by looking for a row containing 'NAMA'
        $headerRowIndex = null;
        $headerMap = []; // column index => header name

        foreach ($rows as $index => $row) {
            $values = $row->map(function ($val) {
                return strtolower(trim((string) $val));
            })->toArray();

            if (in_array('nama', $values) || in_array('name', $values)) {
                $headerRowIndex = $index;

                // Build header map, handle duplicate column names by appending _2, _3, etc
                $seen = [];
                foreach ($values as $colIndex => $val) {
                    if (!empty($val)) {
                        if (isset($seen[$val])) {
                            $seen[$val]++;
                            $headerMap[$colIndex] = $val . '_' . $seen[$val];
                        } else {
                            $seen[$val] = 1;
                            $headerMap[$colIndex] = $val;
                        }
                    }
                }
                break;
            }
        }

        if ($headerRowIndex === null) {
            $this->errors[] = "Header tidak ditemukan. Pastikan ada kolom 'NAMA' di file Excel.";
            return;
        }

        \Log::info('Excel Import Header Map: ' . json_encode($headerMap));

        // Step 2: Parse all data rows
        $dataRows = $rows->slice($headerRowIndex + 1);
        $parsedRows = [];

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 1;
            $rowData = $row->toArray();

            // Build associative array
            $mapped = [];
            foreach ($headerMap as $colIndex => $headerName) {
                $mapped[$headerName] = isset($rowData[$colIndex]) ? trim((string) $rowData[$colIndex]) : '';
            }

            // Extract fields
            $nip = $this->cleanNumericString($this->getMapped($mapped, ['nip/nipeg', 'nipnipeg', 'nip', 'nipeg']));
            $name = $this->getMapped($mapped, ['nama', 'name']);
            $email = $this->getMapped($mapped, ['email', 'e-mail']);
            $gender = $this->getMapped($mapped, ['l/p', 'lp', 'jenis kelamin', 'jenis_kelamin', 'gender']);
            $jabatan = $this->getMapped($mapped, ['jabatan', 'posisi', 'position']);
            $bagian = $this->getMapped($mapped, ['bagian', 'department', 'unit']);

            // First "status" column = status pegawai, second "status_2" = status operasional
            $statusPegawai = $this->getMapped($mapped, ['status', 'status pegawai', 'status_pegawai']);
            $statusOperasional = $this->getMapped($mapped, ['status_2', 'status operasional', 'status_operasional']);

            $tipePresensi = $this->getMapped($mapped, ['tipe presensi', 'tipe_presensi', 'tipe absensi', 'tipe_absensi', 'attendance_type']);
            $nik = $this->cleanNumericString($this->getMapped($mapped, ['nik', 'no ktp', 'ktp', 'nomor induk kependudukan']));
            $alamat = $this->getMapped($mapped, ['alamat', 'address']);
            $noTelepon = $this->cleanNumericString($this->getMapped($mapped, ['no_telepon', 'no telepon', 'no hp', 'no_hp', 'telepon', 'phone', 'hp']));

            // Skip empty rows
            if (empty($nip) && empty($name)) {
                continue;
            }

            $parsedRows[] = [
                'rowNumber' => $rowNumber,
                'nip' => $nip,
                'nik' => $nik,
                'name' => $name,
                'email' => $email,
                'gender' => $gender,
                'jabatan' => $jabatan,
                'bagian' => $bagian,
                'statusPegawai' => $statusPegawai,
                'statusOperasional' => $statusOperasional,
                'tipePresensi' => $tipePresensi,
                'alamat' => $alamat,
                'noTelepon' => $noTelepon,
            ];
        }

        // ============================================================
        // TAHAP 1: Validasi Massal (Pre-validation Pass)
        // Periksa seluruh baris sebelum menyimpan apa pun.
        // ============================================================
        $seenNips = [];
        $seenEmails = [];
        $seenNiks = [];

        foreach ($parsedRows as $parsed) {
            $rowNumber = $parsed['rowNumber'];
            $nip = $parsed['nip'];
            $nik = $parsed['nik'];
            $name = $parsed['name'];
            $email = $parsed['email'];
            $noTelepon = $parsed['noTelepon'];

            // Validasi: NIP wajib dan harus 18 karakter
            if (empty($nip)) {
                $this->errors[] = "Baris {$rowNumber}: NIP kosong.";
            } elseif (strlen($nip) !== 18) {
                $this->errors[] = "Baris {$rowNumber}: NIP '{$nip}' harus 18 karakter (saat ini " . strlen($nip) . " karakter).";
            } elseif (User::where('nip', $nip)->exists()) {
                $this->errors[] = "Baris {$rowNumber}: NIP '{$nip}' ({$name}) sudah terdaftar di database.";
            } elseif (isset($seenNips[$nip])) {
                $this->errors[] = "Baris {$rowNumber}: NIP '{$nip}' duplikat dengan Baris {$seenNips[$nip]} di dalam file.";
            }
            $seenNips[$nip] = $rowNumber;

            // Validasi: NIK wajib dan harus 16 digit
            if (empty($nik)) {
                $this->errors[] = "Baris {$rowNumber}: NIK kosong.";
            } elseif (strlen($nik) !== 16) {
                $this->errors[] = "Baris {$rowNumber}: NIK '{$nik}' harus 16 digit (saat ini " . strlen($nik) . " digit).";
            } elseif (EmployeeProfile::where('nik', $nik)->exists()) {
                $this->errors[] = "Baris {$rowNumber}: NIK '{$nik}' sudah terdaftar di database.";
            } elseif (isset($seenNiks[$nik])) {
                $this->errors[] = "Baris {$rowNumber}: NIK '{$nik}' duplikat dengan Baris {$seenNiks[$nik]} di dalam file.";
            }
            $seenNiks[$nik] = $rowNumber;

            // Validasi: Nama wajib
            if (empty($name)) {
                $this->errors[] = "Baris {$rowNumber}: Nama kosong.";
            }

            // Validasi: Email wajib dan unik
            if (empty($email)) {
                $this->errors[] = "Baris {$rowNumber}: Email kosong.";
            } elseif (User::where('email', $email)->exists()) {
                $this->errors[] = "Baris {$rowNumber}: Email '{$email}' sudah terdaftar di database.";
            } elseif (isset($seenEmails[strtolower($email)])) {
                $this->errors[] = "Baris {$rowNumber}: Email '{$email}' duplikat dengan Baris {$seenEmails[strtolower($email)]} di dalam file.";
            }
            $seenEmails[strtolower($email)] = $rowNumber;

            // Validasi: Nomor Telepon wajib, hanya angka, 10-13 digit
            if (empty($noTelepon)) {
                $this->errors[] = "Baris {$rowNumber}: No. Telepon/HP kosong.";
            } else {
                // Bersihkan karakter non-angka (untuk mengatasi format seperti "0812-3456-7890")
                $cleanPhone = preg_replace('/[^0-9]/', '', $noTelepon);
                if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 13) {
                    $this->errors[] = "Baris {$rowNumber}: No. Telepon/HP '{$noTelepon}' tidak valid (harus 10-13 digit angka, saat ini " . strlen($cleanPhone) . " digit).";
                }
            }
        }

        // ============================================================
        // TAHAP 2: Jika ada error, BATALKAN seluruh proses
        // ============================================================
        if (count($this->errors) > 0) {
            // Tidak menyimpan apa pun — kembalikan errors ke controller
            return;
        }

        // ============================================================
        // TAHAP 3: Simpan semua data dalam satu transaksi
        // ============================================================
        DB::transaction(function () use ($parsedRows) {
            foreach ($parsedRows as $parsed) {
                $noTelepon = preg_replace('/[^0-9]/', '', $parsed['noTelepon']);

                $user = User::create([
                    'name' => $this->normalizeCase($parsed['name']),
                    'nip' => $parsed['nip'],
                    'email' => strtolower($parsed['email']),
                    'password' => bcrypt('Presensi@123'),
                    'role' => 'user',
                ]);

                $user->profile()->create([
                    'nik' => $parsed['nik'],
                    'attendance_type' => $this->parseAttendanceType($parsed['tipePresensi']),
                    'jabatan_id' => $this->resolveMasterDataId('jabatan', $parsed['jabatan']),
                    'bagian_id' => $this->resolveMasterDataId('bagian', $parsed['bagian']),
                    'status_pegawai_id' => $this->resolveMasterDataId('status-pegawai', $parsed['statusPegawai']),
                    'status_operasional_id' => $this->resolveMasterDataId('status_operasional', $parsed['statusOperasional']),
                    'jenis_kelamin' => $this->parseGender($parsed['gender']),
                    'alamat' => $parsed['alamat'] ?: null,
                    'no_telepon' => $noTelepon,
                ]);

                $this->results[] = [
                    'name' => $this->normalizeCase($parsed['name']),
                    'nip' => $parsed['nip'],
                    'status' => 'success',
                ];
            }
        });
    }

    /**
     * Format dan bersihkan angka besar (seperti NIP/NIK) agar tidak terbaca sebagai notasi ilmiah (E+).
     */
    protected function cleanNumericString(string $value): string
    {
        $value = trim($value);
        if (empty($value)) {
            return '';
        }

        // Jika dibaca sebagai notasi ilmiah (contoh: 1.9900101202001E+17)
        if (stripos($value, 'e') !== false) {
            $floatVal = (float) $value;
            if (is_finite($floatVal)) {
                return number_format($floatVal, 0, '.', '');
            }
        }

        return $value;
    }

    /**
     * Get value from mapped array by trying multiple possible header names
     */
    protected function getMapped(array $mapped, array $possibleKeys): string
    {
        foreach ($possibleKeys as $key) {
            if (isset($mapped[$key]) && trim($mapped[$key]) !== '') {
                return trim($mapped[$key]);
            }
        }
        return '';
    }

    /**
     * Normalize casing: "PENGEMUDI" → "Pengemudi", "cleaning service" → "Cleaning Service"
     * Keeps "Non-Operasional" format correctly
     */
    protected function normalizeCase(string $value): string
    {
        if (empty($value)) return '';

        // Handle hyphenated words: "Non-Operasional", "Non-Kontrak"
        return implode('-', array_map(function ($part) {
            return implode(' ', array_map('ucfirst', explode(' ', strtolower($part))));
        }, explode('-', $value)));
    }

    protected function parseGender($value)
    {
        $value = strtoupper(trim($value));
        if (in_array($value, ['L', 'LAKI-LAKI', 'LAKI', 'PRIA', 'M', 'MALE'])) {
            return 'L';
        }
        if (in_array($value, ['P', 'PEREMPUAN', 'WANITA', 'F', 'FEMALE'])) {
            return 'P';
        }
        return null;
    }

    protected function parseAttendanceType($value)
    {
        $value = strtolower(trim($value));
        if (in_array($value, ['shift', 's'])) {
            return 'shift';
        }
        if (in_array($value, ['umum', 'u', 'general'])) {
            return 'umum';
        }
        return 'normal';
    }

    protected function resolveMasterDataId(string $typeSlug, string $value): ?int
    {
        if (empty($value)) return null;

        $normalizedValue = $this->normalizeCase($value);

        $type = str_replace('-', '_', $typeSlug);

        $masterDataValue = \App\Models\MasterData::where('type', $type)
            ->where('value', $normalizedValue)
            ->first();

        return $masterDataValue?->id;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSkipped()
    {
        return $this->skipped;
    }
}
