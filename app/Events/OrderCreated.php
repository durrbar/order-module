<?php

declare(strict_types=1);

namespace Modules\Order\Events;

use Illuminate\Broadcasting\Channel;
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

    public function __construct(
        public Order $order,
        public array $invoiceData,
        public ?User $user
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $eventChannels = [];
        $shopIds = [];
        $vendorIds = [];

        $admins = $this->getAdminUsers();
        if ($admins->isNotEmpty()) {
            foreach ($admins as $user) {
                $eventChannels[] = new PrivateChannel('order.created.'.$user->id);
            }
        }

        if (isset($this->order->products)) {
            foreach ($this->order->products as $product) {
                if (! in_array($product->shop_id, $shopIds, true)) {
                    $vendorShop = Shop::findOrFail($product->shop_id);
                    if (! in_array($vendorShop->owner_id, $vendorIds, true)) {
                        $vendorIds[] = $vendorShop->owner_id;
                    }
                    $shopIds[] = $product->shop_id;
                }
            }
        }

        if ($vendorIds !== []) {
            foreach ($vendorIds as $vendorId) {
                $eventChannels[] = new PrivateChannel('order.created.'.$vendorId);
            }
        }

        return $eventChannels;
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
                if ($settings->options['pushNotification']['all']['order'] === true) {
                    $enableBroadCast = true;
                }
            }

            return $enableBroadCast;
        } catch (DurrbarException $th) {
            throw new DurrbarException(SOMETHING_WENT_WRONG, $th->getMessage());
        }
    }
}
