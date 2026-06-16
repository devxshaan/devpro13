<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('subscriptions.view.own')
            || $user->can('subscriptions.view.any');
    }

    public function view(User $user, Subscription $subscription): bool
    {
       
        if ($user->can('subscriptions.view.any')) {
            return true;
        }

        
        return $user->can('subscriptions.view.own')
            && $user->id === $subscription->user_id;
    }

    public function create(User $user): bool
    {
        return $user->can('subscriptions.create');
    }

    public function update(User $user, Subscription $subscription): bool
    {
        if ($user->can('subscriptions.edit')) {
            return true;
        }

        return false;
    }

    public function cancel(User $user, Subscription $subscription): bool
    {
        
        if ($user->can('subscriptions.cancel')
            && $user->can('subscriptions.view.any')) {
            return true;
        }
        return $user->can('subscriptions.cancel')
            && $user->id === $subscription->user_id
            && $subscription->status === 'active';
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return false;
    }
}