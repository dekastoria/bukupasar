<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Tenant;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

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

    protected function handleRecordCreation(array $data): Model
    {
        /** @var Tenant $tenant */
        $tenant = Tenant::query()
            ->lockForUpdate()
            ->findOrFail($data['tenant_id']);

        if ($tenant->market_id !== $data['market_id']) {
            throw ValidationException::withMessages([
                'tenant_id' => 'Tenant tidak berada pada pasar yang sama.',
            ]);
        }

        if ((int) $data['jumlah'] > (int) $tenant->outstanding) {
            throw ValidationException::withMessages([
                'jumlah' => sprintf(
                    'Jumlah melebihi outstanding tenant (maksimal %s).',
                    $tenant->formatted_outstanding
                ),
            ]);
        }

        $payment = parent::handleRecordCreation($data);

        $tenant->decrement('outstanding', (int) $data['jumlah']);

        return $payment;
    }
}
