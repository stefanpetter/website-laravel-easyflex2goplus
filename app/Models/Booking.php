<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date'
    ];

    protected $fillable = ['name', 'bed_id', 'flexworker_id', 'status', 'date_start', 'date_end'];

    public static function statuses() {
        
        $statuses = [
            "reserved" => array('name' => "Reserved", 'color' => 'primary'),
            "reservedonrequest" => array('name' => "Reserved on request", 'color' => 'info'),
            "pendingarrival" => array('name' => "Pending arrival", 'color' => 'warning'),
            "vacation" => array('name' => "Vacation", 'color' => 'alternate'),
            "maintenance" => array('name' => "Maintenance", 'color' => 'secondary'),
            "renovation" =>array('name' => "Renovation", 'color' => 'secondary'),
            "blocked" => array('name' => "Blocked", 'color' => 'danger')
        ];
        
        return $statuses;
    }

    public function bed() {
        return $this->belongsTo(Bed::class);
    }
    
    public function flexworker() {
        return $this->belongsTo(Flexworker::class);
    }

}