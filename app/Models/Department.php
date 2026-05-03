<?php

namespace App\Models;

use App\Models\Faculty;
use App\Models\Room;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    // konfigurasi
    protected $fillable = [
        'faculty_id',
        'name',
        'code',
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
