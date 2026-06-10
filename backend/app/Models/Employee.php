<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    use HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'branch_id',
        'user_id',
        'manager_id', // Reporting Manager (pointing to users.id)
        'shift_id', // Default Shift
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'profile_picture_url',
        'gender',
        'dob',
        'marital_status',
        'joining_date',
        'status',
        'department_id',
        'designation_id',
        'personal_info',
        'family_info',
        'emergency_contacts',
        'bank_details',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
        'personal_info' => 'array',
        'family_info' => 'array',
        'emergency_contacts' => 'array',
        'bank_details' => 'array',
    ];

    /**
     * Boot the Employee model.
     * Hooks into the creating event to auto-generate the employee_id sequence.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Employee $employee) {
            if (empty($employee->employee_id)) {
                $year = now()->format('Y');
                
                $lastEmployee = DB::table('employees')
                    ->where('company_id', $employee->company_id)
                    ->where('employee_id', 'like', "EMP-{$year}-%")
                    ->orderBy('employee_id', 'desc')
                    ->first();

                if ($lastEmployee && !empty($lastEmployee->employee_id)) {
                    $parts = explode('-', $lastEmployee->employee_id);
                    $lastSeq = (int) end($parts);
                    $nextSeq = $lastSeq + 1;
                } else {
                    $nextSeq = 1;
                }

                $employee->employee_id = sprintf('EMP-%s-%04d', $year, $nextSeq);
            }
        });
    }

    /**
     * Get the user account linked to this employee.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who is this employee's reporting manager.
     *
     * @return BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the employee profile of the reporting manager.
     *
     * @return HasOne
     */
    public function managerProfile(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id', 'manager_id');
    }

    /**
     * Get direct reports (employees who report to this employee's user account).
     *
     * @return HasMany
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id', 'user_id');
    }

    /**
     * Get the department.
     *
     * @return BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get the designation.
     *
     * @return BelongsTo
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    /**
     * Get the assigned shift.
     *
     * @return BelongsTo
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    /**
     * Get the documents uploaded for this employee.
     *
     * @return HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id');
    }

    /**
     * Get the transfer history.
     *
     * @return HasMany
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(EmployeeTransfer::class, 'employee_id');
    }

    /**
     * Get the daily attendance logs.
     *
     * @return HasMany
     */
    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'employee_id');
    }

    /**
     * Get regularization requests submitted.
     *
     * @return HasMany
     */
    public function regularizations(): HasMany
    {
        return $this->hasMany(AttendanceRegularization::class, 'employee_id');
    }
}
