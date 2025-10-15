<?php

namespace App\Filament\Resources\RentalTypeResource\Pages;

use App\Filament\Resources\RentalTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRentalType extends EditRecord
{
    protected static string $resource = RentalTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
