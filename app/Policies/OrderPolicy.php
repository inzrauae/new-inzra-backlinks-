<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * A customer may view only their own orders; an admin may view any.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->role === UserRole::Admin;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only an admin updates order status/payment — customers never edit their own orders.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->role === UserRole::Admin;
    }
}
