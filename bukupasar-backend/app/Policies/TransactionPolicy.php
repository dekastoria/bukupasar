<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin_pusat', 'admin_pasar', 'inputer', 'viewer']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // Must be same market
        if ($transaction->market_id !== $user->market_id) {
            return false;
        }

        return $user->hasAnyRole(['admin_pusat', 'admin_pasar', 'inputer', 'viewer']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin_pusat', 'admin_pasar', 'inputer']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        // Must be same market
        if ($transaction->market_id !== $user->market_id) {
            return false;
        }

        // Admin can always edit
        if ($user->hasAnyRole(['admin_pusat', 'admin_pasar'])) {
            return true;
        }

        // Inputer can edit own transaction within 24 hours
        if ($user->hasRole('inputer')) {
            $isOwner = $transaction->created_by === $user->id;
            $within24h = $transaction->created_at->diffInHours(now()) <= 24;
            
            return $isOwner && $within24h;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        // Same logic as update
        return $this->update($user, $transaction);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Transaction $transaction): bool
    {
        return $user->hasAnyRole(['admin_pusat', 'admin_pasar']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $user->hasRole('admin_pusat');
    }
}
