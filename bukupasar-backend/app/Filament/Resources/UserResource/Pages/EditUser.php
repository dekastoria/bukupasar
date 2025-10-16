<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => UserResource::canDelete($this->getRecord())),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove role from data as it will be assigned after save
        unset($data['role']);

        return $data;
    }

    protected function afterSave(): void
    {
        $role = $this->form->getRawState()['role'] ?? null;

        if ($role) {
            $this->record->syncRoles([$role]);
        }
    }
}
