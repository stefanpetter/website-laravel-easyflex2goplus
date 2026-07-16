<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'planning_import_id',
        'company_name',
        'subsidiary_name',
        'role_name',
        'cost_center_name',
        'work_address',
        'shift_date',
        'day_name',
        'start_time',
        'end_time',
        'shift_start_at',
        'shift_end_at',
        'shift_type_1',
        'shift_type_2',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'shift_start_at' => 'datetime',
        'shift_end_at' => 'datetime',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(PlanningImport::class, 'planning_import_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(PlanningShiftAssignment::class);
    }
}
