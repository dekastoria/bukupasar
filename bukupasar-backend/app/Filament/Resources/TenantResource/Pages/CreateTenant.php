<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tarif = (int) ($data['tarif_sewa'] ?? 0);
        $outstanding = $data['outstanding'] ?? null;

        if ($outstanding === null || $outstanding === '') {
            $data['outstanding'] = $tarif;
        } else {
            $data['outstanding'] = (int) $outstanding;
        }

        return $data;
    }
}
