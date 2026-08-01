<?php

namespace App\Imports;

use App\Models\Salary;
use App\Models\SalaryDeduction;
use App\Models\DeductionType;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalaryImport implements ToCollection
{
    protected $month;
    protected $year;
    protected $overwrite;
    protected $results = [];
    protected $errors = [];

    // Variasi nama header yang dikenali untuk setiap field utama
    protected $headerAliases = [
        'nik'           => ['NIK', 'NIP', 'PNIP', 'NO_INDUK', 'NO INDUK', 'NOMOR INDUK'],
        'nama'          => ['NMPPNPN', 'NAMA PEGAWAI', 'NAMA KARYAWAN', 'NAMA', 'NMPNG', 'NMPN', 'NAME'],
        'gaji_pokok'    => ['GAJI POKOK', 'PENGHASILAN', 'BASE SALARY', 'GAJI', 'PENDAPATAN'],
        'potongan'      => ['POTONGAN KPPN', 'POTONGAN', 'PPH', 'PAJAK', 'TAX'],
        'gaji_diterima' => ['GAJI DITERIMA', 'NITERIMA', 'NET SALARY', 'TAKE HOME PAY', 'THP', 'DITERIMA'],
    ];

    public function __construct($month, $year, $overwrite = false)
    {
        $this->month = $month;
        $this->year = $year;
        $this->overwrite = $overwrite;
    }

    public function collection(Collection $rows)
    {
        // 1. Ambil semua deduction types dari database
        $deductionTypes = DeductionType::all();

        // 2. Cari baris header berdasarkan nama kolom yang dikenali
        $headerRowIndex = null;
        $columnMap = [];
        $deductionColumnMap = []; // mapping: deduction_type_id => column_index

        foreach ($rows as $index => $row) {
            $map = $this->detectHeaders($row);
            if (isset($map['nik']) || isset($map['nama'])) {
                $headerRowIndex = $index;
                $columnMap = $map;

                // Deteksi kolom potongan intern berdasarkan DeductionType di DB
                $deductionColumnMap = $this->detectDeductionColumns($row, $deductionTypes);
                break;
            }
        }

        if ($headerRowIndex === null) {
            $this->errors[] = 'Header tidak ditemukan. Pastikan baris header mengandung kolom NIP/NIK dan NAMA.';
            return;
        }

        // Validasi: minimal harus ada kolom NIP/NAMA
        if (!isset($columnMap['nik']) && !isset($columnMap['nama'])) {
            $this->errors[] = 'Kolom NIK atau NAMA tidak ditemukan di header.';
            return;
        }

        if (!isset($columnMap['gaji_pokok'])) {
            $this->errors[] = 'Kolom Gaji Pokok / PENGHASILAN tidak ditemukan di header. Pastikan header mengandung salah satu: '
                . implode(', ', $this->headerAliases['gaji_pokok']);
            return;
        }

        // 3. Proses data mulai dari baris setelah header
        $dataRows = $rows->slice($headerRowIndex + 1);
        $parsedRows = [];

        // ============================================================
        // TAHAP 1: Validasi Massal (Pre-validation Pass)
        // Periksa seluruh baris sebelum melakukan transaksi penyimpanan
        // ============================================================
        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 1;

            // Ambil NIK/NIP dan Nama dari kolom yang terdeteksi
            $nik = isset($columnMap['nik']) ? trim((string)($row[$columnMap['nik']] ?? '')) : '';
            $nama = isset($columnMap['nama']) ? trim((string)($row[$columnMap['nama']] ?? '')) : '';

            // Skip baris kosong
            if (empty($nik) && empty($nama)) {
                continue;
            }

            // Bersihkan notasi ilmiah sebelum pemrosesan
            $cleanId = $this->cleanNumericString($nik);
            $cleanId = str_replace([' ', '-', '.'], '', $cleanId);

            // Skip jika data pengenal bukan angka (mungkin sub-header atau footer)
            if (!empty($cleanId) && !is_numeric($cleanId)) {
                continue;
            }

            // Cari user
            $user = $this->findUser($nik, $nama);

            if (!$user) {
                $identifier = $cleanId ?: $nama;
                $this->errors[] = "Baris {$rowNumber}: Karyawan NIK/NIP '{$identifier}' / '{$nama}' tidak ditemukan di sistem.";
                continue;
            }

            // Cek apakah sudah ada data untuk periode ini
            $existing = Salary::where('user_id', $user->id)
                ->where('month', $this->month)
                ->where('year', $this->year)
                ->first();

            if ($existing && !$this->overwrite) {
                $this->errors[] = "Baris {$rowNumber}: Data gaji {$user->name} untuk periode ini sudah ada. Centang 'Timpa data' jika ingin menimpa.";
                continue;
            }

            // Ambil nilai gaji dari kolom yang terdeteksi
            $baseSalary = isset($columnMap['gaji_pokok'])
                ? $this->parseNumber($row[$columnMap['gaji_pokok']] ?? 0)
                : 0;

            $potonganKppn = isset($columnMap['potongan'])
                ? $this->parseNumber($row[$columnMap['potongan']] ?? 0)
                : 0;

            // Validasi gaji pokok tidak boleh 0/negatif
            if ($baseSalary <= 0) {
                $this->errors[] = "Baris {$rowNumber}: Gaji pokok {$user->name} bernilai 0 atau kurang.";
                continue;
            }

            // Baca potongan intern dari kolom yang terdeteksi
            $deductionItems = [];
            $totalPotonganIntern = 0;
            foreach ($deductionColumnMap as $typeId => $colIndex) {
                $amount = $this->parseNumber($row[$colIndex] ?? 0);
                if ($amount > 0) {
                    $deductionItems[] = [
                        'deduction_type_id' => $typeId,
                        'amount' => $amount,
                    ];
                    $totalPotonganIntern += $amount;
                }
            }

            // Hitung total deductions (KPPN + intern)
            $totalDeductions = $potonganKppn + $totalPotonganIntern;

            // Gaji diterima: baca dari Excel jika ada, atau hitung manual
            $finalSalary = 0;
            if (isset($columnMap['gaji_diterima'])) {
                $rawFinal = $this->parseNumber($row[$columnMap['gaji_diterima']] ?? 0);
                // Validasi: gaji diterima harus masuk akal (tidak lebih besar dari 10x gaji pokok)
                if ($rawFinal > 0 && $rawFinal <= $baseSalary * 10) {
                    $finalSalary = $rawFinal;
                }
            }

            // Jika gaji diterima tidak valid, hitung manual
            if ($finalSalary <= 0) {
                $finalSalary = $baseSalary - $totalDeductions;
            }

            // Pastikan gaji diterima tidak negatif
            if ($finalSalary < 0) {
                $this->errors[] = "Baris {$rowNumber}: Gaji diterima {$user->name} bernilai negatif (Gaji: {$baseSalary}, Potongan: {$totalDeductions}).";
                continue;
            }

            $parsedRows[] = [
                'user' => $user,
                'existing' => $existing,
                'base_salary' => $baseSalary,
                'potongan_kppn' => $potonganKppn,
                'total_potongan_intern' => $totalPotonganIntern,
                'final_salary' => $finalSalary,
                'deduction_items' => $deductionItems,
            ];
        }

        // ============================================================
        // TAHAP 2: Batal Jika Ada Error
        // ============================================================
        if (count($this->errors) > 0) {
            return;
        }

        // ============================================================
        // TAHAP 3: Simpan Semua dalam Satu Transaksi Database
        // ============================================================
        DB::transaction(function () use ($parsedRows) {
            foreach ($parsedRows as $parsed) {
                $user = $parsed['user'];
                $existing = $parsed['existing'];

                if ($existing && $this->overwrite) {
                    // Hapus data lama beserta potongan intern
                    $existing->salaryDeductions()->delete();
                    $existing->delete();
                }

                $salary = Salary::create([
                    'user_id' => $user->id,
                    'month' => $this->month,
                    'year' => $this->year,
                    'base_salary' => $parsed['base_salary'],
                    'potongan_kppn' => $parsed['potongan_kppn'],
                    'total_potongan_intern' => $parsed['total_potongan_intern'],
                    'final_salary' => $parsed['final_salary'],
                    'created_by' => auth()->id(),
                    'status' => 'draft',
                    'notes' => 'Imported from Excel',
                ]);

                // Simpan potongan intern ke salary_deductions
                foreach ($parsed['deduction_items'] as $item) {
                    SalaryDeduction::create([
                        'salary_id' => $salary->id,
                        'deduction_type_id' => $item['deduction_type_id'],
                        'amount' => $item['amount'],
                    ]);
                }

                $this->results[] = [
                    'name' => $user->name,
                    'nip' => $user->nip ?? '',
                    'base_salary' => $parsed['base_salary'],
                    'final_salary' => $parsed['final_salary'],
                    'status' => 'success',
                    'message' => 'Berhasil diimport'
                ];
            }
        });
    }

    /**
     * Deteksi posisi kolom utama berdasarkan nama header
     */
    protected function detectHeaders($row): array
    {
        $map = [];
        foreach ($row as $colIndex => $cell) {
            $cellValue = strtoupper(trim($cell ?? ''));
            if (empty($cellValue)) continue;

            foreach ($this->headerAliases as $field => $aliases) {
                if (isset($map[$field])) continue;

                foreach ($aliases as $alias) {
                    if (str_contains($cellValue, $alias)) {
                        // Khusus untuk nama, abaikan jika itu kolom NAMATTD (Nama Tanda Tangan/Pejabat)
                        if ($field === 'nama' && str_contains($cellValue, 'NAMATTD')) {
                            continue;
                        }
                        $map[$field] = $colIndex;
                        break;
                    }
                }
            }
        }
        return $map;
    }

    /**
     * Deteksi kolom potongan intern berdasarkan nama DeductionType di database
     * Return: [deduction_type_id => column_index]
     */
    protected function detectDeductionColumns($row, $deductionTypes): array
    {
        $map = [];
        foreach ($row as $colIndex => $cell) {
            $cellValue = strtoupper(trim($cell ?? ''));
            if (empty($cellValue)) continue;

            foreach ($deductionTypes as $type) {
                $typeName = strtoupper(trim($type->name));
                // Cocokkan nama deduction type dengan header kolom
                if (str_contains($cellValue, $typeName) || str_contains($typeName, $cellValue)) {
                    $map[$type->id] = $colIndex;
                }
            }
        }
        return $map;
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
     * Cari user berdasarkan NIK atau Nama
     */
    protected function findUser(string $nik, string $nama): ?User
    {
        $user = null;

        if (!empty($nik)) {
            $cleanId = $this->cleanNumericString($nik);
            $cleanId = str_replace([' ', '-', '.'], '', $cleanId);

            // 1a. Coba cari sebagai NIP (18 digit) di tabel users
            $user = User::where('nip', $cleanId)->first();

            // 1b. Coba cari sebagai NIK (16 digit) di tabel employee_profiles
            if (!$user) {
                $user = User::whereHas('profile', function ($query) use ($cleanId) {
                    $query->where('nik', $cleanId);
                })->first();
            }
        }

        // 2. Fallback: cari berdasarkan nama jika pencarian NIP/NIK tidak ketemu
        if (!$user && !empty($nama)) {
            $cleanNama = trim($nama);
            // 2a. Exact match case-insensitive
            $user = User::whereRaw('LOWER(name) = ?', [strtolower($cleanNama)])->first();
            
            // 2b. LIKE match jika tidak ketemu
            if (!$user) {
                $user = User::where('name', 'like', '%' . $cleanNama . '%')->first();
            }
        }

        return $user;
    }

    /**
     * Parse angka dari Excel
     */
    protected function parseNumber($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        if (is_numeric($value)) {
            return intval(round(floatval($value)));
        }
        // Hapus separator ribuan dan parse
        $value = str_replace(['.', ',', ' '], ['', '.', ''], $value);
        return intval(round(floatval($value))) ?: 0;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
