<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_id',
        'deduction_type_id',
        'amount',
    ];

    public function salary()
    {
        return $this->belongsTo(Salary::class);
    }

    public function type()
    {
        return $this->belongsTo(DeductionType::class, 'deduction_type_id');
    }
}
