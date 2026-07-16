<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'planning_shift_id',
        'worker_registration_number',
        'worker_name',
        'worker_status',
        'planning_status',
        'is_driver',
    ];

    protected $casts = [
        'is_driver' => 'boolean',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(PlanningShift::class, 'planning_shift_id');
    }
}
