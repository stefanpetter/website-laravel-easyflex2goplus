<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'house_id', 'floor', 'size'];

    public function house() {
        return $this->belongsTo(House::class);
    }
    
    public function beds() {
        return $this->hasMany(Bed::class)->orderBy('name');
    }

}