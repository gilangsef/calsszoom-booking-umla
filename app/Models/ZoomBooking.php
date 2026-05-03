<?php

namespace App\Models;

use App\Enums\ZoomBookingLinkStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ZoomBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_code',
        'user_id',
        'booking_date',
        'start_time',
        'end_time',
        'purpose',
        'description',
        'participant_count',
        'status',

        // --- DATA API ZOOM (BARU) ---
        'zoom_link',
        'zoom_meeting_id',
        'zoom_password',
        'zoom_category',

        // --- DATA VERIFIKASI ---
        'verified_by',
        'verified_at',
        'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'status' => ZoomBookingLinkStatus::class,
        'verified_at' => 'datetime',
        'participant_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($booking) => $booking->booking_code ??= self::generateBookingCode());
    }

    public function zoomLink(): BelongsTo { return $this->belongsTo(ZoomLink::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }

    public static function generateBookingCode(): string
    {
        $code = 'ZB-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        return self::where('booking_code', $code)->exists() ? self::generateBookingCode() : $code;
    }

    public function getDurationInMinutes(): int
    {
        $start = Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->getRawOriginal('start_time'));
        $end = Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->getRawOriginal('end_time'));
        return $start->diffInMinutes($end);
    }

    public function getFormattedDuration(): string
    {
        $m = $this->getDurationInMinutes();
        $h = floor($m / 60);
        $rm = $m % 60;
        return ($h > 0 ? "{$h} jam " : "") . ($rm > 0 ? "{$rm} menit" : "");
    }
}
