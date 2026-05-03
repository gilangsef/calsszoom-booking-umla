<?php

namespace App\Models;

use App\Enums\ZoomBookingLinkStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ZoomLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'meeting_id',
        'meeting_url',
        'passcode',
        'host_email',
        'status',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relations
    public function bookings(): HasMany
    {
        return $this->hasMany(ZoomBooking::class);
    }

    public function activeBookings(): HasMany
    {
        return $this->hasMany(ZoomBooking::class)
            // Gunakan Enum::APPROVED (otomatis dikonversi Laravel)
            ->where('status', ZoomBookingLinkStatus::APPROVED)
            ->where('booking_date', '>=', now()->toDateString());
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper Methods
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->is_active;
    }

    public function isAvailableAt($date, $startTime, $endTime): bool
{
    // Cek di tabel zoom_bookings apakah ada yang bentrok
    $exists = ZoomBooking::where('zoom_link_id', $this->id)
        ->where('booking_date', $date)
        ->whereNotIn('status', ['rejected', 'cancelled']) // Kecuali yang sudah batal/ditolak
        ->where(function ($query) use ($startTime, $endTime) {
            $query->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
        })
        ->exists();

    return !$exists;
}

    public function getTodayBookingsCount(): int
    {
        return $this->bookings()
            ->where('booking_date', now()->toDateString())
            // PERBAIKAN DI SINI JUGA
            ->whereIn('status', [ZoomBookingLinkStatus::APPROVED, ZoomBookingLinkStatus::COMPLETED])
            ->count();
    }
}
