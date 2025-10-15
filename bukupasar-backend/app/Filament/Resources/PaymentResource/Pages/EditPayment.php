<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Tenant;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        if ($user && ! $user->hasRole('admin_pusat')) {
            $data['market_id'] = $this->record->market_id;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Tenant $originalTenant */
        $originalTenant = Tenant::query()
            ->lockForUpdate()
            ->findOrFail($record->tenant_id);

        $originalTenant->outstanding += (int) $record->jumlah;
        $originalTenant->save();

        if ((int) $data['tenant_id'] !== $record->tenant_id) {
            /** @var Tenant $targetTenant */
            $targetTenant = Tenant::query()
                ->lockForUpdate()
                ->findOrFail($data['tenant_id']);
        } else {
            $targetTenant = $originalTenant->refresh();
        }

        if ($targetTenant->market_id !== $data['market_id']) {
            throw ValidationException::withMessages([
                'tenant_id' => 'Tenant tidak berada pada pasar yang sama.',
            ]);
        }

        if ((int) $data['jumlah'] > (int) $targetTenant->outstanding) {
            throw ValidationException::withMessages([
                'jumlah' => sprintf(
                    'Jumlah melebihi outstanding tenant (maksimal %s).',
                    $targetTenant->formatted_outstanding
                ),
            ]);
        }

        $updatedRecord = parent::handleRecordUpdate($record, $data);

        $targetTenant->decrement('outstanding', (int) $data['jumlah']);

        return $updatedRecord;
    }
}
