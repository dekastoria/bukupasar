<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use Filament\Resources\Pages\EditRecord;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('outstanding', $data)) {
            $value = $data['outstanding'];

            if ($value === null || $value === '') {
                unset($data['outstanding']);
            } else {
                $data['outstanding'] = (int) $value;
            }
        }

        return $data;
    }
}
