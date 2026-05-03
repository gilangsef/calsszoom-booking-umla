<?php

namespace App\Models;

use App\Enums\DamageReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DamageReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_code',
        'room_id',
        'reported_by',
        'room_booking_id',
        'damage_type',
        'description',
        'severity',
        'photo',
        'status',
        'reported_at',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'status' => DamageReportStatus::class,
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (!$report->report_code) {
                $report->report_code = self::generateReportCode();
            }
            if (!$report->reported_at) {
                $report->reported_at = now();
            }
        });

        static::created(function ($report) {
            // Set room status to under_maintenance if severity is high or critical
            if (in_array($report->severity, ['high', 'critical'])) {
                $report->room->update(['status' => 'under_maintenance']);
            }
        });
    }

    // Relations
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function roomBooking(): BelongsTo
    {
        return $this->belongsTo(RoomBooking::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', DamageReportStatus::PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', DamageReportStatus::IN_PROGRESS);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', DamageReportStatus::RESOLVED);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }

    // Static Methods
    public static function generateReportCode(): string
    {
        $prefix = 'DR';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        
        $code = "{$prefix}-{$date}-{$random}";
        
        while (self::where('report_code', $code)->exists()) {
            $random = strtoupper(Str::random(4));
            $code = "{$prefix}-{$date}-{$random}";
        }
        
        return $code;
    }

    // Helper Methods
    public function markAsInProgress(): void
    {
        $this->update(['status' => DamageReportStatus::IN_PROGRESS]);
    }

    public function markAsResolved($resolvedBy, $notes = null): void
    {
        $this->update([
            'status' => DamageReportStatus::RESOLVED,
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
            'resolution_notes' => $notes,
        ]);

        // Check if there are other pending reports for this room
        $otherPendingReports = DamageReport::where('room_id', $this->room_id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('id', '!=', $this->id)
            ->exists();

        // If no other pending reports, set room back to available
        if (!$otherPendingReports) {
            $this->room->update(['status' => 'available']);
        }
    }

    public function getSeverityLabel(): string
    {
        return match($this->severity) {
            'low' => 'Ringan',
            'medium' => 'Sedang',
            'high' => 'Berat',
            'critical' => 'Kritis',
            default => 'Tidak Diketahui',
        };
    }

    public function getSeverityColor(): string
    {
        return match($this->severity) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'danger',
            default => 'gray',
        };
    }
}
