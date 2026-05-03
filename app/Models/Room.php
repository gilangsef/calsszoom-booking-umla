<?php

namespace App\Models;

use App\Enums\RoomStatus;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes.
     * Cleaned from previous duplications.
     */
    protected $fillable = [
        'code',
        'name',
        'building',
        'floor',
        'capacity',
        'facilities',
        'status',
        'description',
        'image',
        'is_active',
        'location',
        'department_id',
    ];

    /**
     * Type casting for attributes.
     */
    protected $casts = [
        'facilities' => 'array',
        'status' => RoomStatus::class,
        'is_active' => 'boolean',
        'floor' => 'integer',
        'capacity' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function bookings(): HasMany
    {
        return $this->hasMany(RoomBooking::class);
    }

    public function activeBookings(): HasMany
    {
        return $this->hasMany(RoomBooking::class)
            ->whereIn('status', ['verified', 'in_use'])
            ->where('booking_date', '>=', now()->toDateString());
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class);
    }

    public function pendingDamageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class)
            ->where('status', 'pending');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
                     ->where('status', RoomStatus::AVAILABLE);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Menentukan apakah ruangan secara fundamental dapat digunakan (tidak dinonaktifkan).
     */
    public function isAvailable(): bool
    {
        // Ruangan tetap bisa dilihat student asal is_active true.
        // Status 'locked' di DB biasanya hanya untuk maintenance/rusak berat.
        return $this->is_active && $this->status !== RoomStatus::UNDER_MAINTENANCE;
    }

    /**
     * Logika Inti: Mengecek bentrokan jadwal.
     * Menggunakan algoritma overlap: (StartA < EndB) AND (EndA > StartB)
     */
    public function isAvailableAt($date, $startTime, $endTime): bool
    {
        $exists = $this->bookings()
            ->where('booking_date', $date)
            ->where('status', '!=', 'rejected')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();

        return !$exists; // Return true jika TIDAK ada yang bentrok
    }

    /**
     * Mendapatkan jumlah pemakaian hari ini.
     */
    public function getTodayBookingsCount(): int
    {
        return $this->bookings()
            ->where('booking_date', now()->toDateString())
            ->whereIn('status', ['verified', 'in_use', 'completed'])
            ->count();
    }

    /**
     * Format list fasilitas untuk tampilan.
     */
    public function getFacilitiesList(): string
    {
        if (!$this->facilities) {
            return '-';
        }

        return is_array($this->facilities)
            ? implode(', ', $this->facilities)
            : $this->facilities;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
