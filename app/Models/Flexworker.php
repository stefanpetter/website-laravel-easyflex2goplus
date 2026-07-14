<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Flexworker extends Model
{
    use HasFactory;

    protected $fillable = ['relation_id', 'snelstart_id', 'status', 'invoice', 'initials', 'first_name', 'last_name', 'email', 'gender', 'nationality', 'description'];

    public function getNameAttribute(){
        return $this->last_name . ', '. $this->initials .' ('. $this->first_name.')';
    }

    public function bookings() {
        return $this->hasMany(Booking::class);
    }

}