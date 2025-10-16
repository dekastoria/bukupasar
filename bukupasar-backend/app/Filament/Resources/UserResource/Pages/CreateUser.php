<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove role from data as it will be assigned after creation
        unset($data['role']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $role = $this->form->getRawState()['role'] ?? null;

        if ($role) {
            $this->record->syncRoles([$role]);
        }
    }
}
