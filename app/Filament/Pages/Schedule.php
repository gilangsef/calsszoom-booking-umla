<?php

namespace App\Filament\Pages;

use App\Models\Room;
use App\Models\RoomBooking;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema; // Gunakan Schema untuk Filament 5
use Filament\Support\Icons\Heroicon;
use UnitEnum; // Import UnitEnum untuk type hint

class Schedule extends Page implements HasForms
{
    use InteractsWithForms;

    // Tambahkan Type Hint lengkap agar tidak error FatalError
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|UnitEnum|null $navigationGroup = 'Booking';

    protected string $view = 'filament.pages.schedule';

    protected static ?string $title = 'Jadwal';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('View:Schedule');
    }

    /**
     * Form untuk Dropdown Pencarian Ruangan
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                Select::make('room_id')
                    ->label(false)
                    ->placeholder('Cari & Pilih Ruangan...')
                    ->options(Room::where('is_active', true)->pluck('name', 'id'))
                    ->searchable() // Baris cari ada di sini
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->refreshCalendar($state)),
            ]);
    }

    public function refreshCalendar($roomId)
    {
        $this->dispatch('refreshCalendar', events: $this->getEvents($roomId));
    }

    protected function getEvents($roomId = null): array
    {
        $query = RoomBooking::with(['room', 'user']);

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        if (!auth()->user()->isAdmin() && !auth()->user()->isOperasional()) {
            $query->where('user_id', auth()->id());
        }

        return $query->get()->map(function ($booking) {
            return [
                'id' => $booking->id,
                'title' => "{$booking->room->name} ({$booking->user->name})",
                'start' => $booking->booking_date->format('Y-m-d') . 'T' . $booking->start_time,
                'end' => $booking->booking_date->format('Y-m-d') . 'T' . $booking->end_time,
                'color' => $booking->status->getColor() === 'success' ? '#10b981' : '#f59e0b',
            ];
        })->toArray();
    }

    protected function getViewData(): array
    {
        return [
            'events' => $this->getEvents($this->data['room_id'] ?? null),
        ];
    }
}
