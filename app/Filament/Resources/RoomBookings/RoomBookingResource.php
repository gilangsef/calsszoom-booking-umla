<?php

namespace App\Filament\Resources\RoomBookings;

use App\Filament\Resources\RoomBookings\Pages\CreateRoomBooking;
use App\Filament\Resources\RoomBookings\Pages\EditRoomBooking;
use App\Filament\Resources\RoomBookings\Pages\ListRoomBookings;
use App\Filament\Resources\RoomBookings\Pages\ViewRoomBooking;
use App\Filament\Resources\RoomBookings\Schemas\RoomBookingForm;
use App\Filament\Resources\RoomBookings\Schemas\RoomBookingInfolist;
use App\Filament\Resources\RoomBookings\Tables\RoomBookingsTable;
use App\Models\RoomBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

use App\Enums\BookingStatus;
use Illuminate\Support\Facades\Auth;


class RoomBookingResource extends Resource
{
    protected static ?string $model = RoomBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string | UnitEnum | null $navigationGroup = 'Booking';

    protected static ?string $recordTitleAttribute = 'booking_code';

    protected static ?string $navigationLabel = 'Booking Ruangan';

    protected static ?string $modelLabel = 'Booking Ruangan';

    protected static ?string $pluralModelLabel = 'Booking Ruangan';

    public static function form(Schema $schema): Schema
    {
        return RoomBookingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoomBookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomBookingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoomBookings::route('/'),
            // 'create' => CreateRoomBooking::route('/create'),
            // 'view' => ViewRoomBooking::route('/{record}'),
            // 'edit' => EditRoomBooking::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['admin', 'operational', 'room', 'user'])
            ->visibleTo(auth()->user());
    }

    public static function getNavigationBadge(): ?string
{
    // Hanya Admin & Operasional yang bisa melihat jumlah antrean verifikasi
    if (!Auth::user()?->isAdmin() && !Auth::user()?->isOperasional()) {
        return null;
    }

    $count = static::getModel()::where('status', BookingStatus::PENDING)->count();


    // Jika 0, badge tidak akan muncul (null)
    return $count > 0 ? (string) $count : null;
}

public static function getNavigationBadgeColor(): ?string
{
    // Warna kuning/orange untuk menandakan perlu tindakan segera
    return 'warning';
}

    public static function getGloballySearchableAttributes(): array
    {
        return ['booking_code', 'purpose', 'user.name'];
    }
}
