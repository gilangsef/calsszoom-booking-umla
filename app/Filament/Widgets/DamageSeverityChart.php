<?php

namespace App\Filament\Widgets;

use App\Models\DamageReport;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class DamageSeverityChart extends ChartWidget
{
    // HAPUS kata 'static' di sini
    protected ?string $heading = 'Distribusi Keparahan Kerusakan';

    // HAPUS kata 'static' di sini jika ada
    protected ?string $pollingInterval = '15s';

    /**
     * Membatasi akses widget hanya untuk Admin
     */
    public static function canView(): bool
    {
        return Auth::check() && Auth::user()
        // ->isAdmin();
        ->isPimpinan();
    }

    protected function getData(): array
    {
        $data = DamageReport::selectRaw('severity, count(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Laporan',
                    'data' => [
                        (int) ($data['low'] ?? 0),
                        (int) ($data['medium'] ?? 0),
                        (int) ($data['high'] ?? 0),
                        (int) ($data['critical'] ?? 0),
                    ],
                    'backgroundColor' => [
                        '#22c55e', // Green
                        '#eab308', // Yellow
                        '#f97316', // Orange
                        '#ef4444', // Red
                    ],
                ],
            ],
            'labels' => ['Ringan', 'Sedang', 'Berat', 'Kritis'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
