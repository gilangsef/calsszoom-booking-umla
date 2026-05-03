<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Department;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'nim',
        'nip',
        'is_active',
        'department_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Filament Panel Access
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    // Relations
    public function roomBookings(): HasMany
    {
        return $this->hasMany(RoomBooking::class, 'user_id');
    }

    public function verifiedRoomBookingsAsAdmin(): HasMany
    {
        return $this->hasMany(RoomBooking::class, 'verified_by_admin');
    }

    public function verifiedRoomBookingsAsOperational(): HasMany
    {
        return $this->hasMany(RoomBooking::class, 'verified_by_operational');
    }

    public function takenKeys(): HasMany
    {
        return $this->hasMany(RoomBooking::class, 'key_taken_by');
    }

    public function receivedKeys(): HasMany
    {
        return $this->hasMany(RoomBooking::class, 'key_returned_to');
    }

    public function zoomBookings(): HasMany
    {
        return $this->hasMany(ZoomBooking::class, 'user_id');
    }

    public function verifiedZoomBookings(): HasMany
    {
        return $this->hasMany(ZoomBooking::class, 'verified_by');
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'reported_by');
    }

    public function resolvedDamageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'resolved_by');
    }

    // Helper Methods
    public function isPimpinan(): bool
    {
        return $this->hasRole('pimpinan');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isOperasional(): bool
    {
        return $this->hasRole('operasional');
    }

    public function isDosen(): bool
    {
        return $this->hasRole('dosen');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function getIdentityNumber(): ?string
    {
        return $this->nim ?? $this->nip;
    }

    public function getIdentityType(): string
    {
        if ($this->nim) {
            return 'NIM';
        } elseif ($this->nip) {
            return 'NIP';
        }
        return 'ID';
    }

    public function getTodayBookingsCount(): int
    {
        return $this->roomBookings()
            ->where('booking_date', now()->toDateString())
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->count();
    }

    public function getActiveBookingsCount(): int
    {
        return $this->roomBookings()
            ->whereIn('status', ['pending_admin', 'pending_operational', 'verified', 'in_use'])
            ->count();
    }

    /**
     * Mengizinkan login menggunakan email, nim, atau nip.
     */
    public static function findForAuthentication(string $username): ?self
    {
        return self::where('email', $username)
            ->orWhere('nim', $username)
            ->orWhere('nip', $username)
            ->first();
    }

    /**
     * Relationship to Department (Prodi)
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
