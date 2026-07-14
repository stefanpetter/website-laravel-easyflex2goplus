<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SnelstartExport extends Model
{
    protected $fillable = [
        'filename',
        'export_date',
        'total_price',
        'total_price_per_week',
        'booking_count'
    ];

    protected $casts = [
        'export_date' => 'date',
        'total_price' => 'decimal:2',
        'total_price_per_week' => 'decimal:2'
    ];
}
