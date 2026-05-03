<?php

namespace App\Services;

use App\Models\RoomBooking;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class QRCodeService
{
    /**
     * Generate QR Code untuk booking (Hanya Kode Booking)
     */
    public function generateForBooking(RoomBooking $booking): string
    {
        $qrData = $booking->booking_code;

        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($qrData);

        $filename = "qrcodes/booking-{$booking->booking_code}.png";
        Storage::disk('public')->put($filename, $qrCode);

        return $filename;
    }

    /**
     * Generate QR Code sebagai Base64
     */
    public function generateBase64(RoomBooking $booking): string
    {
        $qrData = $booking->booking_code;

        return base64_encode(
            QrCode::format('svg')
                ->size(300)
                ->margin(2)
                ->errorCorrection('H')
                ->generate($qrData)
        );
    }

    /**
     * Validate QR Code data dengan toleransi waktu 1 jam
     */
    public function validateQRData(string $bookingCode): array
    {
        // 1. Cari booking berdasarkan kode string dari QR
        $booking = RoomBooking::where('booking_code', $bookingCode)->first();

        if (!$booking) {
            return [
                'valid' => false,
                'message' => 'Booking Code tidak terdaftar dalam sistem.',
            ];
        }

        // 2. Validasi Hari (Hanya boleh scan di hari H)
        $now = Carbon::now();
        if (!$now->isSameDay($booking->booking_date)) {
            return [
                'valid' => false,
                'message' => 'QR Code ini hanya berlaku untuk tanggal ' . $booking->booking_date->format('d M Y'),
            ];
        }

        // 3. Validasi Status & Toleransi Waktu (Memanggil fungsi di Model)
        // Fungsi isQRValid() di model sudah menangani toleransi 1 jam sebelum & sesudah
        if (!$booking->isQRValid()) {
            $startTime = Carbon::parse($booking->start_time)->subHour()->format('H:i');
            
            if ($booking->status->value === 'verified' && $now->lt(Carbon::parse($booking->start_time)->subHour())) {
                return [
                    'valid' => false,
                    'message' => "Terlalu cepat! Scan pengambilan kunci baru dibuka pukul $startTime (1 jam sebelum jadwal).",
                ];
            }

            return [
                'valid' => false,
                'message' => 'QR Code sudah tidak berlaku, expired, atau status booking tidak sesuai.',
            ];
        }

        return [
            'valid' => true,
            'booking' => $booking,
            'message' => 'QR Code valid.',
        ];
    }

    /**
     * Process scan untuk pengambilan kunci
     */
    public function processTakeKey(RoomBooking $booking, int $operationalId): array
    {
        if (!$booking->canTakeKey()) {
            return [
                'success' => false,
                'message' => 'Status booking tidak mengizinkan pengambilan kunci.',
            ];
        }

        try {
            $booking->markAsInUse($operationalId);
            
            // Update status ruangan di DB menjadi OCCUPIED melalui Enum
            $booking->room->update(['status' => \App\Enums\RoomStatus::OCCUPIED]);

            return [
                'success' => true,
                'message' => 'Kunci berhasil diambil. Selamat menggunakan ruangan.',
                'booking' => $booking->fresh(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Process scan untuk pengembalian kunci
     */
    public function processReturnKey(RoomBooking $booking, int $operationalId): array
    {
        if (!$booking->canReturnKey()) {
            return [
                'success' => false,
                'message' => 'Status booking tidak mengizinkan pengembalian kunci.',
            ];
        }

        try {
            $booking->markAsCompleted($operationalId);

            // Update status ruangan kembali ke AVAILABLE
            $booking->room->update(['status' => \App\Enums\RoomStatus::AVAILABLE]);

            $message = 'Kunci berhasil dikembalikan.';
            if ($booking->is_late) {
                $message .= " Terlambat {$booking->late_minutes} menit.";
            }

            return [
                'success' => true,
                'message' => $message,
                'booking' => $booking->fresh(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}