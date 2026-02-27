<?php

namespace App\Imports;

use App\Models\User;
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

        // Step 2: Process data rows
        $dataRows = $rows->slice($headerRowIndex + 1);

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 1;
            $rowData = $row->toArray();

            // Build associative array
            $mapped = [];
            foreach ($headerMap as $colIndex => $headerName) {
                $mapped[$headerName] = isset($rowData[$colIndex]) ? trim((string) $rowData[$colIndex]) : '';
            }

            // Extract fields
            $nip = $this->getMapped($mapped, ['nip/nipeg', 'nipnipeg', 'nip', 'nipeg']);
            $name = $this->getMapped($mapped, ['nama', 'name']);
            $email = $this->getMapped($mapped, ['email', 'e-mail']);
            $gender = $this->getMapped($mapped, ['l/p', 'lp', 'jenis kelamin', 'jenis_kelamin', 'gender']);
            $jabatan = $this->getMapped($mapped, ['jabatan', 'posisi', 'position']);
            $bagian = $this->getMapped($mapped, ['bagian', 'department', 'unit']);

            // First "status" column = status pegawai, second "status_2" = status operasional
            $statusPegawai = $this->getMapped($mapped, ['status', 'status pegawai', 'status_pegawai']);
            $statusOperasional = $this->getMapped($mapped, ['status_2', 'status operasional', 'status_operasional']);

            $tipeAbsensi = $this->getMapped($mapped, ['tipe absensi', 'tipe_absensi', 'attendance_type']);
            $nik = $this->getMapped($mapped, ['nik', 'no ktp', 'ktp', 'nomor induk kependudukan']);
            $alamat = $this->getMapped($mapped, ['alamat', 'address']);

            // Skip empty rows
            if (empty($nip) && empty($name)) {
                continue;
            }

            // Validasi: NIP wajib
            if (empty($nip)) {
                $this->errors[] = "Baris {$rowNumber}: NIP kosong, dilewati.";
                continue;
            }

            // Cek duplikat NIP
            if (User::where('nip', $nip)->exists()) {
                $this->skipped[] = "Baris {$rowNumber}: NIP '{$nip}' ({$name}) sudah terdaftar, dilewati.";
                continue;
            }

            // Validasi: NIK wajib
            if (empty($nik)) {
                $this->errors[] = "Baris {$rowNumber}: NIK kosong, dilewati.";
                continue;
            }

            // Validasi: Nama wajib
            if (empty($name)) {
                $this->errors[] = "Baris {$rowNumber}: Nama kosong, dilewati.";
                continue;
            }

            // Validasi: Email wajib
            if (empty($email)) {
                $this->errors[] = "Baris {$rowNumber}: Email kosong, dilewati.";
                continue;
            }

            // Cek duplikat email
            if (User::where('email', $email)->exists()) {
                $this->errors[] = "Baris {$rowNumber}: Email '{$email}' sudah terdaftar, dilewati.";
                continue;
            }

            try {
                User::create([
                    'name' => $this->normalizeCase($name),
                    'email' => strtolower($email),
                    'password' => bcrypt('password123'),
                    'role' => 'user',
                    'nip' => $nip,
                    'attendance_type' => $this->parseAttendanceType($tipeAbsensi),
                    'jabatan' => $this->normalizeCase($jabatan) ?: null,
                    'bagian' => $this->normalizeCase($bagian) ?: null,
                    'status_pegawai' => $this->normalizeCase($statusPegawai) ?: null,
                    'status_operasional' => $this->normalizeCase($statusOperasional) ?: null,
                    'jenis_kelamin' => $this->parseGender($gender),
                    'nik' => $nik,
                    'alamat' => $alamat ?: null,
                ]);

                $this->results[] = [
                    'name' => $this->normalizeCase($name),
                    'nip' => $nip,
                    'status' => 'success',
                ];
            } catch (\Exception $e) {
                $this->errors[] = "Baris {$rowNumber}: Gagal import {$name} - " . $e->getMessage();
            }
        }
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
        return 'normal';
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
