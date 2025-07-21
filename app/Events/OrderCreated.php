<?php

namespace Modules\Order\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Exceptions\DurrbarException;
use Modules\Order\Models\Order;
use Modules\Settings\Models\Settings;
use Modules\User\Models\User;
use Modules\User\Traits\UsersTrait;
use Modules\Vendor\Models\Shop;

class OrderCreated implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    use UsersTrait;

    public Order $order;

    public array $invoiceData;

    /**
     * user
     *
     * @var User
     */
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, array $invoiceData, ?User $user)
    {
        $this->order = $order;
        $this->invoiceData = $invoiceData;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $event_channels = $shop_ids = $vendor_ids = [];

        // Notify in admin-end
        $admins = $this->getAdminUsers();
        if (isset($admins)) {
            foreach ($admins as $user) {
                $channel_name = new PrivateChannel('order.created.'.$user->id);
                array_push($event_channels, $channel_name);
            }
        }

        // Notify in vendor-end
        if (isset($this->order->products)) {
            foreach ($this->order->products as $product) {
                if (! in_array($product->shop_id, $shop_ids)) {
                    $vendor_shop = Shop::findOrFail($product->shop_id);
                    if (! in_array($vendor_shop->owner_id, $vendor_ids)) {
                        array_push($vendor_ids, $vendor_shop->owner_id);
                    }
                    array_push($shop_ids, $product->shop_id);
                }
            }
        }

        if (isset($vendor_ids)) {
            foreach ($vendor_ids as $vendor_id) {
                $channel_name = new PrivateChannel('order.created.'.$vendor_id);
                array_push($event_channels, $channel_name);
            }
        }

        return $event_channels;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message' => 'One new order created.',
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        // event's name will be written here.
        return 'order.create.event';
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        try {
            $settings = Settings::first();
            $enableBroadCast = false;

            if (config('shop.pusher.enabled') === null) {
                return false;
            }

            if (isset($settings->options['pushNotification']['all']['order'])) {
                if ($settings->options['pushNotification']['all']['order'] == true) {
                    $enableBroadCast = true;
                }
            }

            return $enableBroadCast;
        } catch (DurrbarException $th) {
            throw new DurrbarException(SOMETHING_WENT_WRONG, $th->getMessage());
        }
    }
}
