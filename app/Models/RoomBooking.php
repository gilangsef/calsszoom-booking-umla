<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RoomBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_code',
        'room_id',
        'user_id',
        'time_slot_id',
        'booking_date',
        'start_time',
        'end_time',
        'purpose',
        'description',
        'participant_count',
        'status',
        'verified_by', // Kolom baru
        'verified_at', // Kolom baru
        'verification_notes', // Kolom baru
        'qr_token',
        'qr_generated_at',
        'qr_expired_at',
        'key_taken_at',
        'key_taken_by',
        'key_returned_at',
        'key_returned_to',
        'is_late',
        'late_minutes',
        'rejection_reason',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'status' => BookingStatus::class,
        'verified_at' => 'datetime',
        // 'verified_admin_at' => 'datetime',
        // 'verified_operational_at' => 'datetime',
        'qr_generated_at' => 'datetime',
        'qr_expired_at' => 'datetime',
        'key_taken_at' => 'datetime',
        'key_returned_at' => 'datetime',
        'is_late' => 'boolean',
        'late_minutes' => 'integer',
        'participant_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (!$booking->booking_code) {
                $booking->booking_code = self::generateBookingCode();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi ke Master Jam (TimeSlot)
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function adminVerifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by_admin'); }
    public function operationalVerifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by_operational'); }
    public function keyTaker(): BelongsTo { return $this->belongsTo(User::class, 'key_taken_by'); }
    public function keyReceiver(): BelongsTo { return $this->belongsTo(User::class, 'key_returned_to'); }
    public function damageReports(): HasMany { return $this->hasMany(DamageReport::class); }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePendingAdmin($query) { return $query->where('status', BookingStatus::PENDING); }
    public function scopeVerified($query) { return $query->where('status', BookingStatus::VERIFIED); }
    public function scopeInUse($query) { return $query->where('status', BookingStatus::IN_USE); }
    public function scopeToday($query) { return $query->where('booking_date', now()->toDateString()); }

    public function scopeVisibleTo($query, $user)
    {
        if ($user->isAdmin() || $user->isOperasional()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Logic & Helpers
    |--------------------------------------------------------------------------
    */

    public static function generateBookingCode(): string
    {
        $prefix = 'RB';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $code = "{$prefix}-{$date}-{$random}";

        while (self::where('booking_code', $code)->exists()) {
            $random = strtoupper(Str::random(4));
            $code = "{$prefix}-{$date}-{$random}";
        }
        return $code;
    }

    /**
     * Validasi waktu scan QR (Toleransi 1 Jam)
     */
    public function isQRValid(): bool
    {
        $now = Carbon::now();

        // Memastikan parsing bersih dari string ke Carbon
        $dateStr = $this->booking_date->format('Y-m-d');
        $fullStart = Carbon::parse($dateStr . ' ' . $this->start_time);
        $fullEnd = Carbon::parse($dateStr . ' ' . $this->end_time);

        // Pengambilan: 1 jam sebelum mulai. Pengembalian: sampai 1 jam setelah selesai.
        $earliest = $fullStart->copy()->subHour();
        $latest = $fullEnd->copy()->addHour();

        return $now->between($earliest, $latest) &&
               in_array($this->status->value, ['verified', 'in_use']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status->value, ['pending_admin', 'pending_operational']);
    }

    public function canTakeKey(): bool
    {
        return $this->status === BookingStatus::VERIFIED && !$this->key_taken_at;
    }

    public function canReturnKey(): bool
    {
        return $this->status === BookingStatus::IN_USE && $this->key_taken_at && !$this->key_returned_at;
    }

    public function calculateLateness(): int
    {
        if (!$this->key_returned_at) return 0;

        $scheduledEnd = Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->end_time);

        if ($this->key_returned_at->lte($scheduledEnd)) {
            return 0;
        }

        return (int) $this->key_returned_at->diffInMinutes($scheduledEnd);
    }

    public function markAsInUse($operationalId): void
    {
        $this->update([
            'status' => BookingStatus::IN_USE,
            'key_taken_at' => now(),
            'key_taken_by' => $operationalId,
        ]);

        $this->room->update(['status' => RoomStatus::OCCUPIED]);
    }

    public function markAsCompleted($operationalId): void
    {
        $lateMinutes = $this->calculateLateness();
        $isLate = $lateMinutes > 0;

        $this->update([
            'status' => $isLate ? BookingStatus::LATE_RETURN : BookingStatus::COMPLETED,
            'key_returned_at' => now(),
            'key_returned_to' => $operationalId,
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
        ]);

        $this->room->update(['status' => RoomStatus::AVAILABLE]);
    }

    public function getFormattedDuration(): string
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $diff = $start->diff($end);

        $parts = [];
        if ($diff->h > 0) $parts[] = "{$diff->h} jam";
        if ($diff->i > 0) $parts[] = "{$diff->i} menit";

        return count($parts) > 0 ? implode(' ', $parts) : '0 menit';
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (Memperbaiki error format() di Blade)
    |--------------------------------------------------------------------------
    */

    public function getStartTimeFormattedAttribute()
    {
        return Carbon::parse($this->start_time)->format('H:i');
    }

    public function getEndTimeFormattedAttribute()
    {
        return Carbon::parse($this->end_time)->format('H:i');
    }

    /**
     * Cek apakah status booking sudah siap diverifikasi operasional
     * (Dipanggil oleh OperasionalDashboardController@verify)
     */
    // public function canBeVerifiedByOperational(): bool
    // {
    //     return $this->status === BookingStatus::PENDING_OPERATIONAL;
    // }

    /**
     * Generate token dan set data QR Code
     * (Dipanggil oleh OperasionalDashboardController@verify)
     */
    public function generateQRToken(): string
    {
        // Menggunakan booking_code sebagai token sesuai QRCodeService kamu
        $token = $this->booking_code;

        $this->update([
            'qr_token' => $token,
            'qr_generated_at' => now(),
            // Expired 1 jam setelah waktu booking berakhir
            'qr_expired_at' => Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->end_time)->addHour(),
        ]);

        return $token;
    }

    // Definisikan Relasi ke User yang memverifikasi
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function admin(): BelongsTo
{
    // Pastikan foreign key-nya benar (misal: admin_id atau verified_admin_by)
    return $this->belongsTo(User::class, 'verified_by_admin');
}

public function operational(): BelongsTo
{
    // Pastikan foreign key-nya benar (misal: operational_id atau verified_operational_by)
    return $this->belongsTo(User::class, 'verified_by_operational');
}
}
