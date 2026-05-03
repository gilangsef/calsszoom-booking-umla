<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RoomBooking;

class RoomBookingTable extends Component
{
    use WithPagination;

    public $search = '';

    // Reset pagination saat search berubah
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function cancelBooking($id)
    {
        $booking = RoomBooking::findOrFail($id);
        if ($booking->canBeCancelled()) {
            $booking->update(['status' => 'cancelled']);
            session()->flash('message', 'Booking berhasil dibatalkan.');
        }
    }

    public function render()
    {
        return view('livewire.student.room-booking-table', [
            'bookings' => RoomBooking::where('user_id', auth()->id())
                ->with('room')
                ->where(function($query) {
                    $query->where('booking_code', 'like', '%' . $this->search . '%')
                          ->orWhere('purpose', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(5)
        ]);
    }
}