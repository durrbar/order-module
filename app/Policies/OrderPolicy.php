<?php

namespace Modules\Order\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Order\Models\Order;
use Modules\User\Models\User;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the user can view any posts.
     */
    public function viewAny(User $user)
    {
        return $user->can('orders.*') || $user->can('orders.view');
    }

    /**
     * Determine if the user can view a specific post.
     */
    public function view(User $user, Order $order)
    {
        return $user->can('orders.*') || $user->can('orders.view');
    }

    /**
     * Determine if the user can create a post.
     */
    public function create(User $user)
    {
        return $user->can('orders.*') || $user->can('orders.create');
    }

    /**
     * Determine if the user can update the post.
     */
    public function update(User $user, Order $order)
    {
        return $user->can('orders.*') || $user->can('orders.edit') || $user->id === $order->user_id;
    }

    /**
     * Determine if the user can delete the post.
     */
    public function delete(User $user, Order $order)
    {
        return $user->can('orders.*') || $user->can('orders.delete') || $user->id === $order->user_id;
    }

    /**
     * Determine if the user can restore the post.
     */
    public function restore(User $user, Order $order)
    {
        return $user->can('orders.*') || $user->can('orders.update');
    }

    /**
     * Determine if the user can permanently delete the post.
     */
    public function forceDelete(User $user, Order $order)
    {
        return $user->can('orders.*') && $user->hasRole(['Super Admin', 'Administrator']);
    }
}
