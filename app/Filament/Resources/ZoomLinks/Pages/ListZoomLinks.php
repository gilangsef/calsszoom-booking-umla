<?php

namespace App\Filament\Resources\ZoomLinks\Pages;

use App\Filament\Resources\ZoomLinks\ZoomLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListZoomLinks extends ListRecords
{
    protected static string $resource = ZoomLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
