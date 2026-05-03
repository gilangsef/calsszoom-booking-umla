<?php

namespace App\Filament\Pages;

use App\Models\RoomBooking;
use App\Services\QRCodeService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use UnitEnum;

class QRScanner extends Page
{
    protected string $view = 'filament.pages.q-r-scanner';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Scan QR Code';
    protected static ?string $title = 'Scan QR Code';
    protected static string | UnitEnum | null $navigationGroup = 'Scan';
    protected static ?int $navigationSort = 4;

    public ?array $lastBooking = null;
    public string $errorMessage = '';

    public static function canAccess(): bool
    {
        // return auth()->user()->isOperasional();
        return auth()->user()->can('View:QRScanner');
    }

    public function processScan(string $qrData): void
    {
        $qrService = app(QRCodeService::class);
        $this->errorMessage = '';
        $this->lastBooking = null;

        try {
            // 1. Validasi Data QR
            $validation = $qrService->validateQRData(json_decode($qrData, true) ?? $qrData);

            if (!$validation['valid']) {
                throw new \Exception($validation['message']);
            }

            $booking = $validation['booking'];
            $statusValue = $booking->status instanceof UnitEnum ? $booking->status->value : $booking->status;

            // 2. Logika Otomatis Berdasarkan Status
            if ($statusValue === 'verified') {
                $result = $qrService->processTakeKey($booking, Auth::id());
                $actionLabel = 'PENGAMBILAN';
            } elseif ($statusValue === 'in_use') {
                $result = $qrService->processReturnKey($booking, Auth::id());
                $actionLabel = 'PENGEMBALIAN';
            } else {
                throw new \Exception("Status booking (" . $booking->status->getLabel() . ") tidak memerlukan aksi scan.");
            }

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // 3. Set Data untuk Tampilan
            $this->lastBooking = [
                'code' => $booking->booking_code,
                'room' => $booking->room->name,
                'user' => $booking->user->name,
                'status' => $booking->status->getLabel(),
                'action' => $actionLabel
            ];

            // Kirim event suara sukses ke browser
            $this->dispatch('play-beep-success');

            Notification::make()
                ->title($actionLabel . ' Berhasil')
                ->success()
                ->send();

        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();

            // Kirim event suara gagal ke browser
            $this->dispatch('play-beep-error');

            Notification::make()
                ->title('Gagal Scan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
