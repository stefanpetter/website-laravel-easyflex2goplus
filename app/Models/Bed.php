<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'room_id', 'device_id', 'device_source'];

    public function room() {
        return $this->belongsTo(Room::class);
    }

    public function bookings() {
        return $this->hasMany(Booking::class);
    }
    
}