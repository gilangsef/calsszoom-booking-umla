<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat User Baru')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Users'),

            'students' => Tab::make('Mahasiswa')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('roles', function ($q) {
                    $q->where('name', 'student'); // Pastikan nama role di Shield adalah 'student'
                }))
                ->badge(\App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count())
                ->icon('heroicon-m-user-group'),

            'lecturers' => Tab::make('Dosen')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('roles', function ($q) {
                    $q->where('name', 'dosen'); // Pastikan nama role di Shield adalah 'lecturer'
                }))
                ->badge(\App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'dosen'))->count())
                ->icon('heroicon-m-academic-cap'),
        ];
    }
}
