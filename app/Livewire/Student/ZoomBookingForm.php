<?php

namespace App\Livewire\Student;

use Livewire\Component;
use App\Models\ZoomLink;
use App\Models\ZoomBooking;

class ZoomBookingForm extends Component
{
    public $zoom_link_id, $booking_date, $start_time, $end_time, $purpose, $description, $participant_count = 1;
    public $is_available = true;

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['zoom_link_id', 'booking_date', 'start_time', 'end_time'])) {
            $this->checkAvailability();
        }
    }

    public function checkAvailability()
    {
        if ($this->zoom_link_id && $this->booking_date && $this->start_time && $this->end_time) {
            $zoomLink = ZoomLink::find($this->zoom_link_id);
            $this->is_available = $zoomLink->isAvailableAt($this->booking_date, $this->start_time, $this->end_time);
        }
    }

    public function save()
    {
        $this->validate([
            'zoom_link_id' => 'required|exists:zoom_links,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'purpose' => 'required|string|max:255',
            'participant_count' => 'required|integer|min:1',
        ]);

        if (!$this->is_available) return;

        ZoomBooking::create([
            'user_id' => auth()->id(),
            'zoom_link_id' => $this->zoom_link_id,
            'booking_date' => $this->booking_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'purpose' => $this->purpose,
            'description' => $this->description,
            'participant_count' => $this->participant_count,
            'status' => 'pending',
        ]);

        $this->dispatch('zoom-created');
        $this->reset();
        session()->flash('success', 'Booking Zoom berhasil dibuat!');
    }

    public function render()
    {
        return view('livewire.student.zoom-booking-form', [
            'zoomLinks' => ZoomLink::where('is_active', true)->get()
        ]);
    }
}