<?php

namespace App\Filament\Resources\ZoomLinks\Pages;

use App\Filament\Resources\ZoomLinks\ZoomLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditZoomLink extends EditRecord
{
    protected static string $resource = ZoomLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
