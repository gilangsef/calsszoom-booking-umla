<?php

namespace App\Filament\Resources\ZoomLinks\Pages;

use App\Filament\Resources\ZoomLinks\ZoomLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageZoomLinks extends ManageRecords
{
    protected static string $resource = ZoomLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->modalWidth('4xl'),
        ];
    }
}
