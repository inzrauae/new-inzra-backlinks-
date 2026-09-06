<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SeoOrder;
use App\Models\User;

class SeoOrderPolicy
{
    /**
     * A customer may view only their own SEO orders; an admin may view any.
     */
    public function view(User $user, SeoOrder $order): bool
    {
        return $user->id === $order->user_id || $user->role === UserRole::Admin;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only an admin manages SEO order status/publications — customers never edit their own orders.
     */
    public function update(User $user, SeoOrder $order): bool
    {
        return $user->role === UserRole::Admin;
    }
}
