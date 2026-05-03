<?php


namespace App\Livewire\Student;

use App\Models\Room;
use App\Models\RoomBooking;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class RoomBookingForm extends Component
{
    // Properties untuk Form
    public $room_id;
    public $booking_date;
    public $start_time;
        public $end_time;
    public $purpose;
    public $description;
    public $participant_count = 1;

    // Status State
    public $is_available = true;
    public $capacity_error = false;

    protected $rules = [
        'room_id' => 'required|exists:rooms,id',
        'booking_date' => 'required|date|after_or_equal:today',
        'start_time' => 'required',
        'end_time' => 'required|after:start_time',
        'purpose' => 'required|string|max:255',
        'participant_count' => 'required|integer|min:1',
    ];

    // Lifecycle Hook: Berjalan otomatis setiap kali ada input yang berubah
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        $this->checkConflict();
    }

    public function checkConflict()
    {
        if ($this->room_id && $this->booking_date && $this->start_time && $this->end_time) {
            $room = Room::find($this->room_id);
            
            // Cek Ketersediaan
            $this->is_available = $room->isAvailableAt($this->booking_date, $this->start_time, $this->end_time);
            
            // Cek Kapasitas
            $this->capacity_error = $this->participant_count > $room->capacity;
        }
    }

    public function save()
    {
        $this->validate();

        if (!$this->is_available || $this->capacity_error) return;

        RoomBooking::create([
            'user_id' => Auth::id(),
            'room_id' => $this->room_id,
            'booking_date' => $this->booking_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'purpose' => $this->purpose,
            'description' => $this->description,
            'participant_count' => $this->participant_count,
            'status' => 'pending_admin',
        ]);

        session()->flash('success', 'Booking ruangan berhasil diajukan!');
        return redirect()->route('student.bookings');
    }

    public function render()
    {
        return view('livewire.student.room-booking-form', [
            'rooms' => Room::where('is_active', true)->where('status', 'available')->get()
        ]);
    }
}