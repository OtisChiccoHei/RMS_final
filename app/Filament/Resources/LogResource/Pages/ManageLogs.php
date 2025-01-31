<?php

namespace App\Filament\Resources\LogResource\Pages;

use App\Filament\Resources\LogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLogs extends ManageRecords
{
    protected static string $resource = LogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
