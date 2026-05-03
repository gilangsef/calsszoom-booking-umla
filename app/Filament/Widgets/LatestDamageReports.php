<?php

namespace App\Filament\Widgets;

use App\Models\DamageReport;
use App\Filament\Resources\DamageReports\DamageReportResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn; // Impor spesifik untuk kolom
use Filament\Actions\Action;     // Impor spesifik untuk aksi tabel
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LatestDamageReports extends BaseWidget
{
    protected static ?string $heading = 'Laporan Kerusakan Terbaru';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DamageReport::query()->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('report_code')
                    ->label('Kode')
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('room.name')
                    ->label('Ruangan'),

                TextColumn::make('damage_type')
                    ->label('Jenis Kerusakan'),

                TextColumn::make('severity')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'success',
                        'medium' => 'warning',
                        'high', 'critical' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('reported_at')
                    ->label('Waktu Lapor')
                    ->dateTime('d M Y, H:i'),
            ])
            ->actions([
                // Sekarang bisa panggil Action::make() langsung dengan bersih
                Action::make('view')
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->url(fn (DamageReport $record): string => DamageReportResource::getUrl('index', [
                        'tableSearch' => $record->report_code
                    ])),
            ])
            ->paginated(false);
    }
}
