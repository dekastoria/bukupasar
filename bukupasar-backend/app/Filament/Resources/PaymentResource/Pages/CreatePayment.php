<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

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
