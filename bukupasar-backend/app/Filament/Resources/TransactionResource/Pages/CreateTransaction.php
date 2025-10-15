<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user) {
            $data['created_by'] = $user->id;

            if (! $user->hasRole('admin_pusat')) {
                $data['market_id'] = $user->market_id;
            }
        }

        return $data;
    }
}
