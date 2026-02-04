<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Salary;
use App\Services\SalaryService;
use App\Imports\SalaryImport;
use App\Exports\SalaryTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

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
        
        // Statistik bulan ini
        $totalUsers = User::where('role', 'user')->count();
        $salariesThisMonth = Salary::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->count();
        $pendingSalaries = $totalUsers - $salariesThisMonth;
        
        // Gaji terbaru yang dibuat
        $recentSalaries = Salary::with('user')
            ->latest()
            ->take(10)
            ->get();
        
        return view('staff.keuangan.dashboard', compact(
            'totalUsers',
            'salariesThisMonth',
            'pendingSalaries',
            'recentSalaries',
            'currentMonth',
            'currentYear'
        ));
    }

    /**
     * List gaji per bulan
     */
    public function salaries(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        $salaries = Salary::with('user')
            ->where('month', $month)
            ->where('year', $year)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $users = User::where('role', 'user')->orderBy('name')->get();
        
        return view('staff.keuangan.salaries.index', compact('salaries', 'users', 'month', 'year'));
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
        ]);
        
        $user = User::findOrFail($request->user_id);
        
        $calculation = $this->salaryService->calculateSalary(
            $user,
            $request->month,
            $request->year,
            $request->base_salary
        );
        
        return view('staff.keuangan.salaries.result', compact('calculation', 'user'));
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
            'deductions' => 'required|numeric|min:0',
            'total_work_days' => 'required|integer',
            'total_late_days' => 'required|integer',
            'total_absent_days' => 'required|integer',
            'final_salary' => 'required|numeric|min:0',
        ]);
        
        $this->salaryService->saveSalary($request->all(), auth()->id());
        
        return redirect()->route('staff.keuangan.salaries')
            ->with('success', 'Data gaji berhasil disimpan!');
    }

    /**
     * Detail gaji
     */
    public function showSalary(Salary $salary)
    {
        $salary->load('user');
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
        $salary->delete();
        
        return redirect()->route('staff.keuangan.salaries')
            ->with('success', 'Data gaji berhasil dihapus!');
    }

    /**
     * Form input gaji manual
     */
    public function inputForm()
    {
        $users = User::where('role', 'user')->orderBy('name')->get();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        return view('staff.keuangan.salaries.input', compact('users', 'currentMonth', 'currentYear'));
    }

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
            'simpanan_wajib' => 'nullable|numeric|min:0',
            'kredit_uang' => 'nullable|numeric|min:0',
            'kredit_toko' => 'nullable|numeric|min:0',
            'dharma_wanita' => 'nullable|numeric|min:0',
            'bpjs' => 'nullable|numeric|min:0',
            'total_potongan_intern' => 'nullable|numeric|min:0',
            'final_salary' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'status' => 'nullable|in:draft,approved,paid',
        ]);

        // Check if salary already exists for this period
        $existing = Salary::where('user_id', $request->user_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($existing) {
            return back()->withInput()
                ->with('error', 'Data gaji untuk karyawan ini pada periode tersebut sudah ada!');
        }

        // Calculate total deductions (KPPN + Intern)
        $totalPotonganIntern = ($request->simpanan_wajib ?? 0) + 
                               ($request->kredit_uang ?? 0) + 
                               ($request->kredit_toko ?? 0) + 
                               ($request->dharma_wanita ?? 0) + 
                               ($request->bpjs ?? 0);
        
        $totalDeductions = ($request->potongan_kppn ?? 0) + $totalPotonganIntern;

        Salary::create([
            'user_id' => $request->user_id,
            'month' => $request->month,
            'year' => $request->year,
            'base_salary' => $request->base_salary,
            'potongan_kppn' => $request->potongan_kppn ?? 0,
            'simpanan_wajib' => $request->simpanan_wajib ?? 0,
            'kredit_uang' => $request->kredit_uang ?? 0,
            'kredit_toko' => $request->kredit_toko ?? 0,
            'dharma_wanita' => $request->dharma_wanita ?? 0,
            'bpjs' => $request->bpjs ?? 0,
            'total_potongan_intern' => $totalPotonganIntern,
            'deductions' => $totalDeductions,
            'final_salary' => $request->final_salary,
            'created_by' => auth()->id(),
            'status' => $request->status ?? 'draft',
            'notes' => $request->notes,
        ]);

        return redirect()->route('staff.keuangan.salaries')
            ->with('success', 'Data gaji berhasil disimpan!');
    }

    /**
     * List semua karyawan untuk kelola data keuangan
     */
    public function users(Request $request)
    {
        $query = User::where('role', 'user');
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('nip', 'like', '%' . $request->search . '%');
            });
        }
        
        $users = $query->orderBy('name')->paginate(15);
        
        return view('staff.keuangan.users.index', compact('users'));
    }

    /**
     * Form edit data keuangan user
     */
    public function editUser(User $user)
    {
        return view('staff.keuangan.users.edit', compact('user'));
    }

    /**
     * Update data keuangan user
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'nip' => 'nullable|string|max:20',
            'npwp' => 'nullable|string|max:30',
            'status_pegawai' => 'nullable|string|max:20',
            'nomor_sk' => 'nullable|string|max:50',
            'tanggal_sk' => 'nullable|date',
            'status_pajak' => 'nullable|string|max:10',
            'nomor_rekening' => 'nullable|string|max:30',
            'nama_bank' => 'nullable|string|max:50',
            'gaji_pokok' => 'nullable|numeric|min:0',
            'alamat' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
        ]);

        $user->update([
            'nip' => $request->nip,
            'npwp' => $request->npwp,
            'status_pegawai' => $request->status_pegawai,
            'nomor_sk' => $request->nomor_sk,
            'tanggal_sk' => $request->tanggal_sk,
            'status_pajak' => $request->status_pajak,
            'nomor_rekening' => $request->nomor_rekening,
            'nama_bank' => $request->nama_bank,
            'gaji_pokok' => $request->gaji_pokok,
            'alamat' => $request->alamat,
            'no_telepon' => $request->no_telepon,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        return redirect()->route('staff.keuangan.users')
            ->with('success', 'Data keuangan karyawan berhasil diupdate!');
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
            'file' => 'required|mimes:xlsx,xls|max:5120',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $import = new SalaryImport($request->month, $request->year);
        
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
            
            return redirect()->route('staff.keuangan.salaries')
                ->with('success', $message)
                ->with('import_errors', $errors);
                
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import file: ' . $e->getMessage());
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
}
