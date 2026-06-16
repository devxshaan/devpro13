<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('orders.view.own')
            || $user->can('orders.view.any');
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->can('orders.view.any')) {
            return true;
        }

        return $user->can('orders.view.own')
            && $user->id === $order->user_id;
    }

    public function create(User $user): bool
    {
        return $user->can('orders.create');
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->can('orders.edit.any')) {
            return true;
        }

        return $user->can('orders.edit.own')
            && $user->id === $order->user_id
            && $order->status === 'draft';
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($user->can('orders.cancel.any')) {
            return true;
        }

        return $user->can('orders.cancel.own')
            && $user->id === $order->user_id
            && in_array($order->status, ['pending', 'confirmed']);
    }

    public function delete(User $user, Order $order): bool
    {
        return false;
    }
}