<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'name', 'status', 'snf_beds', 'snf_status', 'price', 'grootboek_nr', 'description', 'gbo'];

    public function group() {
        return $this->belongsTo(Group::class);
    }

    public function rooms() {
        return $this->hasMany(Room::class)->orderBy('name');
    }

}