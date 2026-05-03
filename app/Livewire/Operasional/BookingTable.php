<?php

namespace App\Livewire\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RoomBooking;

class BookingTable extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedGedung = '';
    public $selectedLantai = '';

    // Sinkronisasi dengan tombol reset
    public function resetFilters()
    {
        $this->reset(['search', 'selectedGedung', 'selectedLantai']);
        $this->resetPage();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSelectedGedung() { $this->resetPage(); }
    public function updatingSelectedLantai() { $this->resetPage(); }

    public function render()
    {
        $query = RoomBooking::query()->with(['room', 'user']);

        // Filter Search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('booking_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', fn($qu) => $qu->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('room', fn($qr) => $qr->where('name', 'like', '%' . $this->search . '%'));
            });
        }

        // Filter Gedung - Pastikan value di DB sesuai (misal: "A")
        if ($this->selectedGedung) {
            $query->whereHas('room', function($q) {
                $q->where('building', $this->selectedGedung);
            });
        }

        // Filter Lantai
        if ($this->selectedLantai) {
            $query->whereHas('room', function($q) {
                $q->where('floor', $this->selectedLantai);
            });
        }

        return view('livewire.operasional.booking-table', [
            'bookings' => $query->latest()->paginate(10)
        ]);
    }
}
