<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

/**
 * Admin new-order alert (R16/FR-055). Delivered via the database channel and surfaced through
 * Filament polling — no WebSockets required.
 */
class NewOrderNotification extends Notification
{
    public function __construct(public readonly Order $order)
    {
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total' => $this->order->final_total,
            'title' => 'طلب جديد '.$this->order->order_number,
        ];
    }
}
