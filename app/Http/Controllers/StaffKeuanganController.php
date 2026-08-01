<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Salary;
use App\Models\DeductionType;
use App\Models\SalaryDeduction;
use App\Services\SalaryService;
use App\Imports\SalaryImport;
use App\Exports\SalaryTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StaffKeuanganController extends Controller
{
    protected $salaryService;
    
    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    /**
     * Dashboard Staff Keuangan
     */
    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Statistik
        $totalUsers = User::where('role', 'user')->count();
        
        // Gaji bulan ini - by status
        $salariesThisMonth = Salary::where('month', $currentMonth)
            ->where('year', $currentYear);
        
        $paidCount = (clone $salariesThisMonth)->where('status', 'paid')->count();
        $pendingCount = $totalUsers - (clone $salariesThisMonth)->count(); // belum diinput
        
        // Total gaji bulan ini
        $totalSalaryThisMonth = Salary::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->sum('final_salary');
        
        // Gaji terbaru yang dibuat
        $recentSalaries = Salary::with('user')
            ->latest()
            ->take(10)
            ->get();
        
        return view('staff.keuangan.dashboard', compact(
            'totalUsers',
            'paidCount',
            'pendingCount',
            'recentSalaries',
            'totalSalaryThisMonth'
        ));
    }

    /**
     * List gaji per bulan
     */
    public function salaries(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        $status = $request->get('status');
        
        $query = Salary::with('user')
            ->where('month', $month)
            ->where('year', $year);
        
        // Filter berdasarkan status
        if ($status) {
            $query->where('status', $status);
        }
        
        // Cari berdasarkan nama atau NIK
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }
        
        $salaries = $query->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $users = User::where('role', 'user')->orderBy('name')->get();
        
        return view('staff.keuangan.salaries.index', compact('salaries', 'users', 'month', 'year', 'status'));
    }

    /**
     * Form hitung gaji
     */
    public function calculateForm()
    {
        $users = User::where('role', 'user')->orderBy('name')->get();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        return view('staff.keuangan.salaries.calculate', compact('users', 'currentMonth', 'currentYear'));
    }

    /**
     * Proses hitung gaji
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
        ]);
        
        $user = User::findOrFail($request->user_id);
        
        $base = $request->base_salary;
        $allowances = $request->allowances ?? 0;
        $deductions = $request->deductions ?? 0;
        $total = $base + $allowances - $deductions;
        
        $result = [
            'user_id' => $user->id,
            'month' => $request->month,
            'year' => $request->year,
            'base' => $base,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'total' => max(0, $total),
        ];
        
        return view('staff.keuangan.salaries.result', compact('result', 'user'));
    }

    /**
     * Simpan hasil kalkulasi gaji
     */
    public function storeSalary(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'base_salary' => 'required|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'final_salary' => 'required|numeric|min:0',
        ]);
        
        $salary = $this->salaryService->saveSalary($request->all(), auth()->id());
        
        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => get_class($salary),
            'model_id' => $salary->id,
            'description' => "Menghitung & menyimpan data gaji pegawai '{$salary->user->name}' untuk periode {$salary->month}/{$salary->year}",
            'old_values' => null,
            'new_values' => [
                'user_name' => $salary->user->name,
                'period' => "{$salary->month}/{$salary->year}",
                'base_salary' => $salary->base_salary,
                'final_salary' => $salary->final_salary
            ],
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('staff.keuangan.salaries')
            ->with('success', 'Data gaji berhasil disimpan!');
    }

    /**
     * Detail gaji
     */
    public function showSalary(Salary $salary)
    {
        $salary->load('user', 'salaryDeductions.type');
        return view('staff.keuangan.salaries.detail', compact('salary'));
    }

    /**
     * Update status gaji
     */
    public function updateStatus(Request $request, Salary $salary)
    {
        $request->validate([
            'status' => 'required|in:draft,approved,paid',
        ]);
        
        $salary->update(['status' => $request->status]);
        
        return back()->with('success', 'Status gaji berhasil diupdate!');
    }

    /**
     * Hapus gaji
     */
    public function deleteSalary(Salary $salary)
    {
        $userName = $salary->user->name ?? 'Unknown';
        $period = "{$salary->month}/{$salary->year}";

        $oldValues = [
            'user_name' => $userName,
            'period' => $period,
            'base_salary' => $salary->base_salary,
            'final_salary' => $salary->final_salary
        ];

        // Hapus notifikasi terkait gaji ini
        \App\Models\Notification::where('type', 'salary')
            ->where('user_id', $salary->user_id)
            ->where('message', 'like', '%' . $salary->period . '%')
            ->delete();

        $salary->salaryDeductions()->delete();
        $salary->delete();

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => get_class($salary),
            'model_id' => $salary->id,
            'description' => "Menghapus data gaji pegawai '{$userName}' periode {$period}",
            'old_values' => $oldValues,
            'new_values' => null,
            'ip_address' => request()->ip(),
        ]);
        
        return redirect()->route('staff.keuangan.salaries')
            ->with('success', 'Data gaji berhasil dihapus!');
    }

    /**
     * Hapus gaji massal
     */
    public function bulkDeleteSalaries(Request $request)
    {
        $request->validate([
            'salary_ids' => 'required|array|min:1',
            'salary_ids.*' => 'exists:salaries,id',
        ]);

        $deletedSalariesInfo = [];
        $count = 0;
        foreach ($request->salary_ids as $id) {
            $salary = Salary::find($id);
            if ($salary) {
                $deletedSalariesInfo[] = [
                    'id' => $salary->id,
                    'user_name' => $salary->user->name ?? 'Unknown',
                    'period' => "{$salary->month}/{$salary->year}",
                    'final_salary' => $salary->final_salary
                ];

                // Hapus notifikasi terkait
                \App\Models\Notification::where('type', 'salary')
                    ->where('user_id', $salary->user_id)
                    ->where('message', 'like', '%' . $salary->period . '%')
                    ->delete();

                $salary->salaryDeductions()->delete();
                $salary->delete();
                $count++;
            }
        }

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => Salary::class,
            'model_id' => null,
            'description' => "Menghapus {$count} data gaji pegawai secara massal",
            'old_values' => $deletedSalariesInfo,
            'new_values' => null,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('staff.keuangan.salaries')
            ->with('success', "{$count} data gaji berhasil dihapus!");
    }

    /**
     * List karyawan untuk input gaji (halaman dashboard)
     */
    public function inputForm(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        $bagian = $request->get('bagian');
        $jabatan = $request->get('jabatan');
        
        $query = User::where('role', 'user');
        
        if ($bagian) {
            $query->whereHas('bagianValue', function($q) use ($bagian) {
                $q->where('value', $bagian);
            });
        }
        if ($jabatan) {
            $query->whereHas('jabatanValue', function($q) use ($jabatan) {
                $q->where('value', $jabatan);
            });
        }
        
        // Dapatkan semua pengguna beserta status gaji mereka untuk periode ini
        $users = $query->orderBy('name')
            ->get()
            ->map(function ($user) use ($month, $year) {
                $salary = Salary::where('user_id', $user->id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();
                $user->salary_status = $salary ? 'done' : 'pending';
                $user->salary_exists = $salary ? true : false;
                $user->salary_id = $salary ? $salary->id : null;
                return $user;
            });
        
        $doneCount = $users->where('salary_status', 'done')->count();
        $pendingCount = $users->where('salary_status', 'pending')->count();
        
        return view('staff.keuangan.salaries.input-list', compact('users', 'month', 'year', 'doneCount', 'pendingCount'));
    }

    /**
     * Form bulk input gaji
     */
    public function bulkInputForm(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        $users = User::where('role', 'user')
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($month, $year) {
                $salary = Salary::where('user_id', $user->id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();
                $user->salary_exists = $salary !== null;
                return $user;
            });
        
        return view('staff.keuangan.salaries.bulk', compact('users', 'month', 'year'));
    }

    /**
     * Store bulk gaji
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'selected_users' => 'required|array|min:1',
        ]);

        $month = $request->month;
        $year = $request->year;
        $selectedUsers = $request->selected_users;
        $successCount = 0;
        $errorCount = 0;

        foreach ($selectedUsers as $userId) {
            // Periksa apakah data gaji sudah ada
            $exists = Salary::where('user_id', $userId)
                ->where('month', $month)
                ->where('year', $year)
                ->exists();
            
            if ($exists) {
                $errorCount++;
                continue;
            }

            // Uraikan nilai-nilai (parse values)
            $baseStr = $request->input("base_salary.{$userId}", '0');
            $kppnStr = $request->input("potongan_kppn.{$userId}", '0');
            $internStr = $request->input("potongan_intern.{$userId}", '0');
            $finalStr = $request->input("final_salary.{$userId}", '0');

            $baseSalary = intval(str_replace('.', '', $baseStr));
            $potonganKppn = intval(str_replace('.', '', $kppnStr));
            $potonganIntern = intval(str_replace('.', '', $internStr));
            $finalSalary = intval(str_replace('.', '', $finalStr));

            Salary::create([
                'user_id' => $userId,
                'month' => $month,
                'year' => $year,
                'base_salary' => $baseSalary,
                'potongan_kppn' => $potonganKppn,
                'total_potongan_intern' => $potonganIntern,
                'final_salary' => $finalSalary,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            $successCount++;
        }

        if ($successCount > 0) {
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => Salary::class,
                'model_id' => null,
                'description' => "Menginput secara massal {$successCount} data gaji pegawai untuk periode {$month}/{$year}",
                'old_values' => null,
                'new_values' => [
                    'month' => $month,
                    'year' => $year,
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                ],
                'ip_address' => request()->ip(),
            ]);
        }

        $message = "{$successCount} data gaji berhasil disimpan.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} data dilewati (sudah ada).";
        }

        return redirect()->route('staff.keuangan.salaries')
            ->with('success', $message);
    }

    /**
     * Form input gaji untuk satu karyawan
     */
    public function inputFormSingle(User $user, Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        $deductionTypes = DeductionType::where('is_active', true)->get();
        
        // Periksa apakah data gaji sudah ada
        $existingSalary = Salary::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
            
        if ($existingSalary) {
            return redirect()->route('staff.keuangan.salaries.input')
                ->with('error', 'Gaji untuk karyawan ini periode ' . $month . '/' . $year . ' sudah ada!');
        }
        
        return view('staff.keuangan.salaries.input', compact('user', 'month', 'year', 'deductionTypes'));
    }

    /**
     * Store gaji dari input manual (sesuai format slip gaji TVRI)
     */
    /**
     * Store gaji dari input manual (sesuai format slip gaji TVRI)
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'base_salary' => 'required|numeric|min:0',
            'potongan_kppn' => 'nullable|numeric|min:0',
            // Validasi potongan dinamis ditangani secara manual atau validasi array
            'deduction_ids' => 'nullable|array',
            'deduction_ids.*' => 'exists:deduction_types,id',
            'deduction_amounts' => 'nullable|array',
            'deduction_amounts.*' => 'numeric|min:0',
            
            'final_salary' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'status' => 'nullable|in:draft,approved,paid',
        ]);

        // Periksa apakah gaji sudah ada untuk periode ini
        $existing = Salary::where('user_id', $request->user_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($existing) {
            return back()->withInput()
                ->with('error', 'Data gaji untuk karyawan ini pada periode tersebut sudah ada!');
        }

        // 1. Calculate Total Intern Deductions
        $totalPotonganIntern = 0;
        $itemsToSave = [];

        if ($request->has('deduction_ids') && $request->has('deduction_amounts')) {
            foreach ($request->deduction_ids as $index => $typeId) {
                $amount = $request->deduction_amounts[$index] ?? 0;
                if ($amount > 0) {
                    $totalPotonganIntern += $amount;
                    $itemsToSave[] = [
                        'deduction_type_id' => $typeId,
                        'amount' => $amount,
                    ];
                }
            }
        }

        $baseSalary = $request->base_salary;
        $potonganKppn = $request->potongan_kppn ?? 0;
        $totalPotongan = $potonganKppn + $totalPotonganIntern;

        // Validasi logika: Potongan tidak boleh lebih besar dari gaji pokok
        if ($totalPotongan > $baseSalary) {
            return back()->withInput()->withErrors([
                'base_salary' => 'Total potongan (Rp ' . number_format($totalPotongan, 0, ',', '.') . ') tidak boleh lebih besar dari gaji pokok (Rp ' . number_format($baseSalary, 0, ',', '.') . ').'
            ]);
        }

        // Validasi logika: Gaji Bersih harus sesuai perhitungan
        $expectedFinalSalary = $baseSalary - $totalPotongan;
        $actualFinalSalary = $request->final_salary;
        
        if (abs($expectedFinalSalary - $actualFinalSalary) > 1) { // toleransi pembulatan
            return back()->withInput()->withErrors([
                'final_salary' => 'Gaji diterima tidak sesuai dengan perhitungan. Seharusnya: Rp ' . number_format($expectedFinalSalary, 0, ',', '.') . '.'
            ]);
        }

        DB::beginTransaction();
        try {
            // 2. Create Salary Record
            $salary = Salary::create([
                'user_id' => $request->user_id,
                'month' => $request->month,
                'year' => $request->year,
                'base_salary' => $request->base_salary,
                'potongan_kppn' => $potonganKppn,
                'total_potongan_intern' => $totalPotonganIntern,
                'final_salary' => $request->final_salary,
                'created_by' => auth()->id(),
                'status' => $request->status ?? 'draft',
                'notes' => $request->notes,
            ]);

            // 3. Save Salary Deductions
            foreach ($itemsToSave as $item) {
                SalaryDeduction::create([
                    'salary_id' => $salary->id,
                    'deduction_type_id' => $item['deduction_type_id'],
                    'amount' => $item['amount'],
                ]);
            }

            DB::commit();

            // Catat log ke ActivityLog
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => get_class($salary),
                'model_id' => $salary->id,
                'description' => "Menginput data gaji manual pegawai '{$salary->user->name}' untuk periode {$salary->month}/{$salary->year}",
                'old_values' => null,
                'new_values' => [
                    'user_name' => $salary->user->name,
                    'period' => "{$salary->month}/{$salary->year}",
                    'base_salary' => $salary->base_salary,
                    'potongan_kppn' => $salary->potongan_kppn,
                    'total_potongan_intern' => $salary->total_potongan_intern,
                    'final_salary' => $salary->final_salary
                ],
                'ip_address' => request()->ip(),
            ]);

            return redirect()->route('staff.keuangan.salaries')
                ->with('success', 'Data gaji berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Form edit gaji
     */
    public function editSalary(Salary $salary)
    {
        $salary->load(['user', 'salaryDeductions.type']);
        $deductionTypes = DeductionType::where('is_active', true)->get();
        
        return view('staff.keuangan.salaries.edit', compact('salary', 'deductionTypes'));
    }

    /**
     * Update gaji
     */
    public function updateSalary(Request $request, Salary $salary)
    {
        $validated = $request->validate([
            'base_salary' => 'required|numeric|min:0|max:999999999999',
            'potongan_kppn' => 'nullable|numeric|min:0|max:999999999999',
            'total_potongan_intern' => 'nullable|numeric|min:0|max:999999999999',
            'final_salary' => 'required|numeric|min:0|max:999999999999',
            'deduction_ids' => 'nullable|array',
            'deduction_ids.*' => 'exists:deduction_types,id',
            'deduction_amounts' => 'nullable|array',
            'deduction_amounts.*' => 'nullable|numeric|min:0|max:999999999999',
            'notes' => 'nullable|string|max:500',
        ], [
            'base_salary.required' => 'Gaji pokok wajib diisi.',
            'base_salary.numeric' => 'Gaji pokok harus berupa angka.',
            'base_salary.min' => 'Gaji pokok tidak boleh negatif.',
            'base_salary.max' => 'Gaji pokok melebihi batas maksimum.',
            'potongan_kppn.numeric' => 'Potongan KPPN harus berupa angka.',
            'potongan_kppn.min' => 'Potongan KPPN tidak boleh negatif.',
            'potongan_kppn.max' => 'Potongan KPPN melebihi batas maksimum.',
            'total_potongan_intern.numeric' => 'Total potongan intern harus berupa angka.',
            'total_potongan_intern.min' => 'Total potongan intern tidak boleh negatif.',
            'final_salary.required' => 'Gaji diterima wajib diisi.',
            'final_salary.numeric' => 'Gaji diterima harus berupa angka.',
            'final_salary.min' => 'Gaji diterima tidak boleh negatif.',
            'final_salary.max' => 'Gaji diterima melebihi batas maksimum.',
            'deduction_amounts.*.numeric' => 'Nilai potongan harus berupa angka.',
            'deduction_amounts.*.min' => 'Nilai potongan tidak boleh negatif.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ]);

        // Validasi logika: Potongan tidak boleh lebih besar dari gaji pokok
        $baseSalary = $request->base_salary;
        $potonganKppn = $request->potongan_kppn ?? 0;
        $totalPotonganIntern = $request->total_potongan_intern ?? 0;
        $totalPotongan = $potonganKppn + $totalPotonganIntern;

        if ($totalPotongan > $baseSalary) {
            return back()->withInput()->withErrors([
                'total_potongan' => 'Total potongan (Rp ' . number_format($totalPotongan, 0, ',', '.') . ') tidak boleh lebih besar dari gaji pokok (Rp ' . number_format($baseSalary, 0, ',', '.') . ').'
            ]);
        }

        // Validasi logika: Final salary harus sesuai perhitungan
        $expectedFinalSalary = $baseSalary - $totalPotongan;
        $actualFinalSalary = $request->final_salary;
        
        if (abs($expectedFinalSalary - $actualFinalSalary) > 1) { // toleransi pembulatan
            return back()->withInput()->withErrors([
                'final_salary' => 'Gaji diterima tidak sesuai dengan perhitungan. Seharusnya: Rp ' . number_format($expectedFinalSalary, 0, ',', '.') . '.'
            ]);
        }

        $oldValues = [
            'base_salary' => $salary->base_salary,
            'potongan_kppn' => $salary->potongan_kppn,
            'total_potongan_intern' => $salary->total_potongan_intern,
            'final_salary' => $salary->final_salary,
        ];

        try {
            DB::beginTransaction();

            $salary->update([
                'base_salary' => $request->base_salary,
                'potongan_kppn' => $request->potongan_kppn ?? 0,
                'total_potongan_intern' => $request->total_potongan_intern ?? 0,
                'final_salary' => $request->final_salary,
                'notes' => $request->notes,
            ]);

            // Hapus potongan yang ada lalu buat kembali
            $salary->salaryDeductions()->delete();

            if ($request->deduction_ids && $request->deduction_amounts) {
                $deductions = array_map(function ($id, $amount) {
                    return [
                        'deduction_type_id' => $id,
                        'amount' => $amount ?? 0,
                    ];
                }, $request->deduction_ids, $request->deduction_amounts);

                $deductions = array_filter($deductions, fn($d) => $d['amount'] > 0);

                foreach ($deductions as $item) {
                    SalaryDeduction::create([
                        'salary_id' => $salary->id,
                        'deduction_type_id' => $item['deduction_type_id'],
                        'amount' => $item['amount'],
                    ]);
                }
            }

            DB::commit();

            // Catat log ke ActivityLog
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => get_class($salary),
                'model_id' => $salary->id,
                'description' => "Memperbarui data gaji pegawai '{$salary->user->name}' periode {$salary->month}/{$salary->year}",
                'old_values' => $oldValues,
                'new_values' => [
                    'base_salary' => $salary->base_salary,
                    'potongan_kppn' => $salary->potongan_kppn,
                    'total_potongan_intern' => $salary->total_potongan_intern,
                    'final_salary' => $salary->final_salary,
                ],
                'ip_address' => request()->ip(),
            ]);

            return redirect()->route('staff.keuangan.salaries.show', $salary)
                ->with('success', 'Data gaji berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * List semua karyawan + status input gaji (gabungan halaman)
     */
    public function users(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        $query = User::where('role', 'user');
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('nip', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('bagian')) {
            $query->whereHas('bagianValue', function($q) use ($request) {
                $q->where('value', $request->bagian);
            });
        }
        
        $users = $query->orderBy('name')
            ->get()
            ->map(function ($user) use ($month, $year) {
                $salary = Salary::where('user_id', $user->id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();
                $user->salary_exists = $salary ? true : false;
                $user->salary_id = $salary ? $salary->id : null;
                return $user;
            });
        
        $totalUsers = $users->count();
        $completedCount = $users->where('salary_exists', true)->count();
        
        // Get unique bagians for filter
        $bagians = \App\Models\MasterData::where('type', 'bagian')
            ->where('is_active', true)
            ->pluck('value')
            ->sort();
        
        return view('staff.keuangan.users.index', compact(
            'users', 'month', 'year', 'totalUsers', 'completedCount', 'bagians'
        ));
    }

    /**
     * Lihat detail data karyawan (read-only)
     */
    public function showUser(User $user)
    {
        return view('staff.keuangan.users.show', compact('user'));
    }



    /**
     * Tampilkan form import Excel
     */
    public function importForm()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        return view('staff.keuangan.salaries.import', compact('currentMonth', 'currentYear'));
    }

    /**
     * Process import Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        // Validasi ekstensi file
        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return redirect()->route('staff.keuangan.salaries.import.form')
                ->with('error', "Format file tidak didukung ({$ext}). Gunakan file .xlsx, .xls, atau .csv");
        }

        $overwrite = $request->boolean('overwrite');
        $import = new SalaryImport($request->month, $request->year, $overwrite);
        
        try {
            Excel::import($import, $request->file('file'));
            
            $results = $import->getResults();
            $errors = $import->getErrors();
            
            $successCount = count($results);
            $errorCount = count($errors);
            
            $message = "Import selesai! {$successCount} data berhasil diimport.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} data gagal.";
            }

            // Catat log ke ActivityLog
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => Salary::class,
                'model_id' => null,
                'description' => "Melakukan import data gaji dari Excel untuk periode {$request->month}/{$request->year} ({$message})",
                'old_values' => null,
                'new_values' => [
                    'month' => $request->month,
                    'year' => $request->year,
                    'overwrite' => $overwrite,
                    'success_count' => $successCount,
                    'error_count' => $errorCount
                ],
                'ip_address' => request()->ip(),
            ]);

            \Log::info('Salary Import completed', [
                'success' => $successCount,
                'errors' => $errorCount,
                'error_details' => $errors,
                'month' => $request->month,
                'year' => $request->year,
                'overwrite' => $overwrite,
            ]);

            if ($successCount === 0 && $errorCount > 0) {
                return redirect()->route('staff.keuangan.salaries.import.form')
                    ->with('error', "Import gagal! Tidak ada data yang berhasil diimport. {$errorCount} data bermasalah.")
                    ->with('import_errors', $errors);
            }

            return redirect()->route('staff.keuangan.salaries.import.form')
                ->with('success', $message)
                ->with('import_errors', $errorCount > 0 ? $errors : null);
                
        } catch (\Exception $e) {
            \Log::error('Salary Import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('staff.keuangan.salaries.import.form')
                ->with('error', 'Gagal import file: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        $filename = 'template_gaji_' . date('Y-m-d') . '.xlsx';
        return Excel::download(new SalaryTemplateExport, $filename);
    }

    /**
     * Export rekap gaji ke Excel
     */
    public function exportSalaries(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        $filename = 'rekap_gaji_' . $month . '_' . $year . '.xlsx';
        
        return Excel::download(
            new \App\Exports\SalaryExport($month, $year), 
            $filename
        );
    }

    /**
     * Tanda tangan slip gaji (individual)
     */
    public function signSalary(Salary $salary)
    {
        $user = auth()->user();

        if (!$user->signature) {
            return back()->with('error', 'Anda belum upload tanda tangan. Silakan upload terlebih dahulu.');
        }

        $salary->update([
            'signed_by' => $user->id,
            'signed_at' => now(),
            'status' => 'paid',
        ]);

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => get_class($salary),
            'model_id' => $salary->id,
            'description' => "Menandatangani slip gaji pegawai '{$salary->user->name}' periode {$salary->month}/{$salary->year}",
            'old_values' => ['status' => 'draft', 'signed_by' => null],
            'new_values' => ['status' => 'paid', 'signed_by' => $user->id],
            'ip_address' => request()->ip(),
        ]);

        // Kirim notifikasi ke pegawai bahwa slip gaji sudah ditandatangani
        \App\Services\NotificationService::salarySigned($salary);

        return back()->with('success', 'Slip gaji berhasil ditandatangani!');
    }

    /**
     * Tanda tangan massal slip gaji per periode
     */
    public function bulkSignSalaries(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $user = auth()->user();

        if (!$user->signature) {
            return back()->with('error', 'Anda belum upload tanda tangan. Silakan upload terlebih dahulu.');
        }

        // Ambil daftar salary yang akan di-TTD untuk kirim notifikasi
        $unsignedSalaries = Salary::where('month', $request->month)
            ->where('year', $request->year)
            ->whereNull('signed_by')
            ->get();

        $count = Salary::where('month', $request->month)
            ->where('year', $request->year)
            ->whereNull('signed_by')
            ->update([
                'signed_by' => $user->id,
                'signed_at' => now(),
                'status' => 'paid',
            ]);

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => Salary::class,
            'model_id' => null,
            'description' => "Menandatangani {$count} slip gaji secara massal untuk periode {$request->month}/{$request->year}",
            'old_values' => null,
            'new_values' => [
                'month' => $request->month,
                'year' => $request->year,
                'signed_count' => $count
            ],
            'ip_address' => request()->ip(),
        ]);

        // Kirim notifikasi ke setiap pegawai
        foreach ($unsignedSalaries as $salary) {
            $salary->refresh(); // reload data setelah update
            \App\Services\NotificationService::salarySigned($salary);
        }

        return back()->with('success', "{$count} slip gaji berhasil ditandatangani!");
    }

    /**
     * Halaman kelola tanda tangan
     */
    public function signaturePage()
    {
        return view('staff.keuangan.signature.index');
    }

    /**
     * Upload tanda tangan staff keuangan
     */
    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = auth()->user();

        // Hapus tanda tangan lama jika ada
        if ($user->signature) {
            \Storage::disk('public')->delete($user->signature);
        }

        $path = $request->file('signature')->store('signatures', 'public');
        $user->update(['signature' => $path]);

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => get_class($user),
            'model_id' => $user->id,
            'description' => "Mengunggah tanda tangan digital baru staff keuangan",
            'old_values' => null,
            'new_values' => ['signature_path' => $path],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Tanda tangan berhasil diupload!');
    }

}

