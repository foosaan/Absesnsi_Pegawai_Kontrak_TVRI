<?php

namespace App\Http\Controllers;

use App\Models\DeductionType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeductionTypeController extends Controller
{
    /**
     * Display a listing of deduction types.
     */
    public function index()
    {
        $deductionTypes = DeductionType::orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();
            
        return view('staff.keuangan.deductions.index', compact('deductionTypes'));
    }

    /**
     * Store a newly created deduction type in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:deduction_types,name',
            'description' => 'nullable|string',
        ]);

        DeductionType::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('staff.keuangan.deductions.index')
            ->with('success', 'Jenis potongan berhasil ditambahkan.');
    }

    /**
     * Update the specified deduction type in storage.
     */
    public function update(Request $request, DeductionType $deductionType)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('deduction_types')->ignore($deductionType->id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $deductionType->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('staff.keuangan.deductions.index')
            ->with('success', 'Jenis potongan berhasil diperbarui.');
    }

    /**
     * Remove the specified deduction type from storage.
     */
    public function destroy(DeductionType $deductionType)
    {
        $deductionType->delete();

        return redirect()->route('staff.keuangan.deductions.index')
            ->with('success', 'Jenis potongan berhasil dihapus.');
    }
}
