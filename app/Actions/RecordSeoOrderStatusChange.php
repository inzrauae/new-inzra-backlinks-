<?php

namespace App\Actions;

use App\Enums\SeoOrderStatus;
use App\Models\SeoOrder;
use App\Models\User;

class RecordSeoOrderStatusChange
{
    public function handle(SeoOrder $order, SeoOrderStatus $to, ?User $changedBy = null, ?string $note = null): void
    {
        if ($order->order_status === $to) {
            return;
        }

        $from = $order->order_status;

        $order->update(['order_status' => $to]);

        $order->statusHistory()->create([
            'from_status' => $from->value,
            'to_status' => $to->value,
            'changed_by' => $changedBy?->id,
            'note' => $note,
            'created_at' => now(),
        ]);
    }
}
