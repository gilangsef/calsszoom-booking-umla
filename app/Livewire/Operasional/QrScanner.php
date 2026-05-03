<?php

namespace App\Livewire\Operasional;

use Livewire\Component;
use App\Models\RoomBooking;
use App\Services\QRCodeService;

class QrScanner extends Component
{
    public $scanType = 'take'; // take atau return
    public $lastBooking = null;
    public $errorMessage = '';

    public function processScan($qrData)
    {
        $qrService = app(QRCodeService::class);
        $this->errorMessage = '';
        $this->lastBooking = null;
        
        try {
            $validation = $qrService->validateQRData($qrData);

            if (!$validation['valid']) {
                $this->errorMessage = $validation['message'];
                return;
            }

            $booking = $validation['booking'];
            
            // LOGIKA OTOMATIS BERDASARKAN STATUS
            if ($booking->status->value === 'verified') {
                // Jika status verified, otomatis proses ambil kunci
                $result = $qrService->processTakeKey($booking, auth()->id());
            } elseif ($booking->status->value === 'in_use') {
                // Jika status in_use, otomatis proses balik kunci
                $result = $qrService->processReturnKey($booking, auth()->id());
            } else {
                $this->errorMessage = "Status booking (" . $booking->status->getLabel() . ") tidak memerlukan aksi scan.";
                return;
            }

            if ($result['success']) {
                $this->lastBooking = [
                    'code' => $booking->booking_code,
                    'room' => $booking->room->name,
                    'user' => $booking->user->name,
                    'status' => $booking->status->getLabel(),
                    'action' => ($booking->status->value === 'verified') ? 'PENGAMBILAN' : 'PENGEMBALIAN'
                ];
                $this->dispatch('scan-success');
            } else {
                $this->errorMessage = $result['message'];
            }
        } catch (\Exception $e) {
            $this->errorMessage = "Gagal memproses QR: " . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.operasional.qr-scanner');
    }
}