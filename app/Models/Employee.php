<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'email', 'phone', 'address', 'place_of_birth',
        'date_of_birth', 'gender', 'religion', 'marital_status', 'id_number',
        'tax_id', 'bank_account', 'bank_name', 'department_id', 'position_id',
        'supervisor_id', 'join_date', 'exit_date', 'status', 'employment_type',
        'salary', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'join_date' => 'date',
            'exit_date' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
