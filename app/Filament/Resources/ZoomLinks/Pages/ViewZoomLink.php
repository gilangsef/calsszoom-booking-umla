<?php

namespace App\Filament\Resources\ZoomLinks\Pages;

use App\Filament\Resources\ZoomLinks\ZoomLinkResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewZoomLink extends ViewRecord
{
    protected static string $resource = ZoomLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
