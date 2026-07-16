<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_file',
        'iso_week',
        'iso_year',
        'row_count',
        'shift_count',
        'assignment_count',
        'imported_at',
    ];

    protected $casts = [
        'imported_at' => 'datetime',
    ];

    public function shifts(): HasMany
    {
        return $this->hasMany(PlanningShift::class);
    }
}
