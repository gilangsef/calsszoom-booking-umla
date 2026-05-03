<?php

namespace App\Models;

use App\Models\RoomBooking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    // konfigurasi
    protected $fillable = [
        'name',
        'total_sks',
        'duration',
        'is_active',
    ];

    public function roomBookings(): HasMany
    {
        return $this->hasMany(RoomBooking::class);
    }
}
