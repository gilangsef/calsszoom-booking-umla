<?php

namespace App\Filament\Resources\RoomBookings\Schemas;

use App\Enums\BookingStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoomBookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // SECTION 1: INFORMASI PEMINJAMAN (HEADER)
                Section::make('Informasi Peminjaman')
                    ->icon('heroicon-o-ticket')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('booking_code')
                                ->label('Kode Booking')
                                ->weight('black')
                                ->fontFamily('mono')
                                ->color('primary')
                                ->copyable()
                                ->copyMessage('Kode berhasil disalin!'),

                            TextEntry::make('status')
                                ->label('Status Peminjaman')
                                ->badge()
                                ->formatStateUsing(fn (BookingStatus $state): string => $state->getLabel())
                                ->icon(fn ($state) => $state instanceof BookingStatus ? $state->getIcon() : null)
                                ->color(fn (BookingStatus $state): string => $state->getColor()),

                            TextEntry::make('room.name')
                                ->label('Ruangan')
                                ->icon('heroicon-m-home-modern')
                                ->weight('bold'),
                        ]),

                        Grid::make(3)->schema([
                            TextEntry::make('user.name')
                                ->label('Nama Peminjam')
                                ->icon('heroicon-m-user'),

                            TextEntry::make('booking_date')
                                ->label('Tanggal Pakai')
                                ->date('d F Y')
                                ->icon('heroicon-m-calendar'),

                            TextEntry::make('time_range')
                                ->label('Estimasi Waktu')
                                ->icon('heroicon-m-clock')
                                ->state(fn ($record) =>
                                    substr($record->start_time, 0, 5) . ' - ' . substr($record->end_time, 0, 5) . ' WIB'
                                ),
                        ])->extraAttributes(['class' => 'mt-4 pt-4 border-t border-gray-100']),
                    ]),

                // SECTION 2: DETAIL KEGIATAN & AKSES KUNCI
                Section::make('Detail Kegiatan & Akses Kunci')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        TextEntry::make('purpose')
                            ->label('Tujuan Peminjaman')
                            ->markdown()
                            ->columnSpanFull(),

                        Grid::make(3)->schema([
                            TextEntry::make('participant_count')
                                ->label('Kapasitas Peserta')
                                ->numeric()
                                ->suffix(' Orang')
                                ->icon('heroicon-m-users'),

                            TextEntry::make('rejection_reason')
                                ->label('Alasan Penolakan')
                                ->color('danger')
                                ->icon('heroicon-m-exclamation-triangle')
                                ->visible(fn ($record) => $record->status === BookingStatus::REJECTED)
                                ->columnSpan(2), // Ambil 2 kolom sisanya agar lega
                        ])->extraAttributes(['class' => 'mt-4 pt-4 border-t border-gray-100']),

                        Grid::make(3)->schema([
                            IconEntry::make('is_late')
                                ->label('Status Keterlambatan')
                                ->boolean()
                                ->trueColor('danger')
                                ->falseColor('success'),

                            TextEntry::make('late_minutes')
                                ->label('Durasi Telat')
                                ->suffix(' Menit')
                                ->color('danger')
                                ->visible(fn ($record) => $record->is_late),

                            TextEntry::make('qr_expired_at')
                                ->label('Token QR Expired')
                                ->dateTime('H:i')
                                ->badge()
                                ->color('warning')
                                ->icon('heroicon-m-qr-code'),
                        ])->extraAttributes(['class' => 'mt-4 pt-4 border-t border-gray-100']),
                    ]),

                // SECTION 3: VERIFIKASI & LOG PENGGUNAAN
                Section::make('Audit Trail & Log Ruangan')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('user.name') // Asumsi ini relasi ke admin
                                ->label('Diverifikasi Oleh')
                                ->icon('heroicon-m-check-badge')
                                ->placeholder('Belum Diverifikasi'),

                            TextEntry::make('verified_at')
                                ->label('Waktu Verifikasi')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('-'),

                            TextEntry::make('verification_notes')
                                ->label('Catatan Petugas')
                                ->placeholder('Tidak ada catatan'),
                        ]),

                        Grid::make(2)->schema([
                            TextEntry::make('key_taken_at')
                                ->label('Waktu Check-in (Mulai)')
                                ->dateTime('d M Y, H:i')
                                ->icon('heroicon-m-arrow-right-on-rectangle')
                                ->color('warning')
                                ->placeholder('Belum check-in'),

                            TextEntry::make('key_returned_at')
                                ->label('Waktu Check-out (Selesai)')
                                ->dateTime('d M Y, H:i')
                                ->icon('heroicon-m-arrow-left-on-rectangle')
                                ->color('success')
                                ->placeholder('Belum selesai'),
                        ])->extraAttributes(['class' => 'mt-4 pt-4 border-t border-gray-100']),
                    ])
                    ->collapsible(),
            ]);
    }
}
