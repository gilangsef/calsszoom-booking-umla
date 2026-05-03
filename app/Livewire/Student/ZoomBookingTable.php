<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ZoomBooking;

class ZoomBookingTable extends Component
{
    use WithPagination;

    public $search = '';

    // Listener agar saat modal simpan data, table otomatis refresh tanpa reload
    protected $listeners = ['zoom-created' => '$refresh'];

    // Reset pagination ke halaman 1 setiap kali user mengetik di search bar
    public function updatingSearch() 
    { 
        $this->resetPage(); 
    }

    /**
     * Fungsi untuk membatalkan booking
     */
    public function cancelBooking($id)
    {
        $booking = ZoomBooking::where('user_id', auth()->id())->findOrFail($id);

        // Pastikan hanya bisa hapus jika status masih pending
        if ($booking->status->value === 'pending') {
            $booking->delete();
            
            // Opsional: Kirim browser event untuk notifikasi (Toast)
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Booking berhasil dibatalkan!'
            ]);
        }
    }

    public function render()
    {
        $bookings = ZoomBooking::with('zoomLink')
            ->where('user_id', auth()->id())
            ->where(function($query) {
                $query->where('purpose', 'like', '%' . $this->search . '%')
                      // Tambahkan pencarian kode booking di sini
                      ->orWhere('booking_code', 'like', '%' . $this->search . '%') 
                      ->orWhereHas('zoomLink', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.student.zoom-booking-table', compact('bookings'));
    }
}