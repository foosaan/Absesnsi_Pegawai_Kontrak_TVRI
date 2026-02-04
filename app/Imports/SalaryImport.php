<?php

namespace App\Imports;

use App\Models\Salary;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;

class SalaryImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $month;
    protected $year;
    protected $results = [];
    protected $errors = [];

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena header row + 0-indexed
            
            // Skip empty rows
            if (empty($row['nip']) && empty($row['nama'])) {
                continue;
            }

            // Cari user berdasarkan NIP atau nama
            $user = null;
            if (!empty($row['nip'])) {
                $user = User::where('nip', $row['nip'])->first();
            }
            if (!$user && !empty($row['nama'])) {
                $user = User::where('name', 'like', '%' . $row['nama'] . '%')->first();
            }

            if (!$user) {
                $this->errors[] = "Baris {$rowNumber}: Karyawan '{$row['nip']}' / '{$row['nama']}' tidak ditemukan";
                continue;
            }

            // Cek apakah sudah ada data untuk periode ini
            $existing = Salary::where('user_id', $user->id)
                ->where('month', $this->month)
                ->where('year', $this->year)
                ->first();

            if ($existing) {
                $this->errors[] = "Baris {$rowNumber}: Data gaji {$user->name} untuk periode ini sudah ada";
                continue;
            }

            // Hitung total potongan intern
            $simpananWajib = $this->parseNumber($row['simpanan_wajib'] ?? 0);
            $kreditUang = $this->parseNumber($row['kredit_uang'] ?? 0);
            $kreditToko = $this->parseNumber($row['kredit_toko'] ?? 0);
            $dharmaWanita = $this->parseNumber($row['dharma_wanita'] ?? 0);
            $bpjs = $this->parseNumber($row['bpjs'] ?? 0);
            $totalPotonganIntern = $simpananWajib + $kreditUang + $kreditToko + $dharmaWanita + $bpjs;

            $gajiPokok = $this->parseNumber($row['gaji_pokok'] ?? 0);
            $potonganKppn = $this->parseNumber($row['potongan_kppn'] ?? 0);
            $totalDeductions = $potonganKppn + $totalPotonganIntern;
            $finalSalary = $this->parseNumber($row['gaji_diterima'] ?? ($gajiPokok - $totalDeductions));

            try {
                Salary::create([
                    'user_id' => $user->id,
                    'month' => $this->month,
                    'year' => $this->year,
                    'base_salary' => $gajiPokok,
                    'potongan_kppn' => $potonganKppn,
                    'simpanan_wajib' => $simpananWajib,
                    'kredit_uang' => $kreditUang,
                    'kredit_toko' => $kreditToko,
                    'dharma_wanita' => $dharmaWanita,
                    'bpjs' => $bpjs,
                    'total_potongan_intern' => $totalPotonganIntern,
                    'deductions' => $totalDeductions,
                    'final_salary' => $finalSalary,
                    'created_by' => auth()->id(),
                    'status' => 'draft',
                    'notes' => 'Imported from Excel',
                ]);

                $this->results[] = [
                    'name' => $user->name,
                    'status' => 'success',
                    'message' => 'Berhasil diimport'
                ];
            } catch (\Exception $e) {
                $this->errors[] = "Baris {$rowNumber}: Gagal menyimpan data {$user->name} - " . $e->getMessage();
            }
        }
    }

    protected function parseNumber($value)
    {
        if (is_numeric($value)) {
            return floatval($value);
        }
        // Remove thousands separator (. atau ,) dan parse
        $value = str_replace(['.', ','], ['', '.'], $value);
        return floatval($value) ?: 0;
    }

    public function rules(): array
    {
        return [
            '*.gaji_pokok' => 'nullable',
        ];
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
