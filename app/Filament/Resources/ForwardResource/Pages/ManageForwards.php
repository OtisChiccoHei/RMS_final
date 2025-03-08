<?php

namespace App\Filament\Resources\ForwardResource\Pages;

use App\Filament\Resources\ForwardResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageForwards extends ManageRecords
{
    protected static string $resource = ForwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
