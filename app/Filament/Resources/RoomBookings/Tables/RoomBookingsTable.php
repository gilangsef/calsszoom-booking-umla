<?php

namespace App\Filament\Resources\RoomBookings\Tables;

use App\Enums\BookingStatus;
use App\Models\RoomBooking;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class RoomBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::getTableColumns())
            ->poll('10s')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options(BookingStatus::class),

                SelectFilter::make('building')
                    ->label('Gedung')
                    ->options(fn () => \App\Models\Room::distinct()->pluck('building', 'building')->toArray())
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('room', fn ($q) => $q->where('building', $data['value']));
                        }
                    }),
            ])
            ->actions(self::getActions())
            // ->bulkActions([
            //     BulkActionGroup::make([
            //         DeleteBulkAction::make(),
            //     ]),
            // ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function getTableColumns(): array
    {
        return [
            TextColumn::make('booking_code')
                ->label('Kode')
                ->searchable()
                ->sortable()
                ->badge()
                ->weight('bold')
                ->color('primary')
                ->copyable(),

            TextColumn::make('user.name')
                ->label('Peminjam')
                ->searchable()
                ->visible(fn() => Auth::user()?->isAdmin())
                // ->visible(fn () => auth()->user()->can('view_any_room::booking'))
                ->description(fn (RoomBooking $record) => $record->purpose),

            TextColumn::make('room.name')
                ->label('Ruangan')
                ->badge()
                ->color('gray'),

            TextColumn::make('booking_date')
                    ->label('Jadwal')
                    ->date('d M Y')
                    ->description(fn (RoomBooking $record): string =>
                        ($record->start_time && $record->end_time)
                            ? \Carbon\Carbon::parse($record->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($record->end_time)->format('H:i')
                            : '-'
                    )
                    ->sortable(),

                // TextColumn::make('booking_date')
                // ->label('Waktu')
                // ->formatStateUsing(fn (RoomBooking $record) =>
                //     $record->booking_date->format('d M') . ', ' .
                //     \Carbon\Carbon::parse($record->start_time)->format('H:i') . ' - ' .
                //     \Carbon\Carbon::parse($record->end_time)->format('H:i')
                // )
                // ->sortable(['booking_date', 'start_time']),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->icon(fn ($state) => $state->getIcon())
                ->formatStateUsing(fn ($state) => $state->getLabel())
                ->color(fn ($state) => $state->getColor())
                ->sortable(),


            // TextColumn::make('qr_token')
            //     ->label('Status QR')
            //     ->badge()
            //     ->color(fn ($state) => $state ? 'success' : 'gray')
            //     ->formatStateUsing(fn ($state) => $state ? 'Tersedia' : 'Belum Ada'),

            TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected static function getActions(): array
    {
        return [
            ActionGroup::make([
                ViewAction::make()
                ->label('Lihat'),

                EditAction::make()
                ->label('Edit'),


                // ACTION: AMBIL KUNCI (MANUAL)
                Action::make('take_key_manual')
                    ->label('Ambil Kunci')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('Apakah kunci ruangan sudah diserahkan ke peminjam secara manual?')
                    ->action(function (RoomBooking $record) {
                        $record->update([
                            'status' => BookingStatus::IN_USE,
                            'actual_start_time' => now(), // Mencatat waktu mulai aktual
                        ]);

                        Notification::make()
                            ->title('Status: Sedang Digunakan')
                            ->body('Kunci telah diambil secara manual.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (RoomBooking $record) =>
                        auth()->user()->can('Verify:Booking') &&
                        $record->status === BookingStatus::VERIFIED
                    ),

                // ACTION: KEMBALIKAN KUNCI (MANUAL)
                Action::make('return_key_manual')
                    ->label('Kembalikan Kunci')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalDescription('Apakah kunci ruangan sudah diterima kembali?')
                    ->action(function (RoomBooking $record) {
                        $record->update([
                            'status' => BookingStatus::COMPLETED,
                            'actual_end_time' => now(), // Mencatat waktu selesai aktual
                        ]);

                        Notification::make()
                            ->title('Status: Selesai')
                            ->body('Kunci telah dikembalikan secara manual.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (RoomBooking $record) =>
                        auth()->user()->can('Verify:Booking') &&
                        $record->status === BookingStatus::IN_USE
                    ),

                // ACTION: VERIFIKASI (Update ke kolom baru)
                Action::make('verify_booking')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('verification_notes')
                            ->label('Catatan Verifikasi')
                            ->placeholder('Opsional: Berikan catatan untuk peminjam...')
                    ])
                    ->action(function (RoomBooking $record, array $data) {
                        $record->update([
                            'status' => BookingStatus::VERIFIED,
                            'verified_by' => auth()->id(), // Kolom baru yang lebih simpel
                            'verified_at' => now(),        // Kolom baru
                            'qr_token' => $record->generateQRToken(),
                            'qr_generated_at' => now(),
                            'verification_notes' => $data['verification_notes'] ?? null, // Kolom baru
                        ]);

                        // Opsional: Kunci ruangan agar tidak bisa dipesan di jam yang sama (jika ada logic-nya)
                        $record->room->update(['status' => 'locked']);

                        Notification::make()
                            ->title('Booking Berhasil Diverifikasi')
                            ->body('Status diperbarui dan QR Code telah diterbitkan.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (RoomBooking $record) =>
                        auth()->user()->can('Verify:Booking') &&
                        $record->status === BookingStatus::PENDING
                    ),

                // ACTION: REJECT
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (RoomBooking $record, array $data) {
                        $record->update([
                            'status' => BookingStatus::REJECTED,
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->title('Booking Ditolak')
                            ->danger()
                            ->send();
                    })
                    ->visible(fn (RoomBooking $record) =>
                        auth()->user()->can('Verify:Booking') &&
                        $record->status === BookingStatus::PENDING
                    ),

                Action::make('view_qr')
                    ->label('Lihat QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->visible(fn (RoomBooking $record) =>
                        $record->qr_token !== null &&
                        in_array($record->status, [BookingStatus::VERIFIED, BookingStatus::IN_USE])
                    )
                    ->modalContent(fn (RoomBooking $record) => view(
                        'filament.resources.room-bookings.qr-modal',
                        ['record' => $record]
                    ))
                    ->modalSubmitAction(false),

               DeleteAction::make()
                    ->label('Hapus'),
            ]),
        ];
    }
}
