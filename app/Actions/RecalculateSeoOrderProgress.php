<?php

namespace App\Actions;

use App\Enums\SeoOrderStatus;
use App\Mail\SeoOrderStatusChanged;
use App\Models\SeoOrder;
use Illuminate\Support\Facades\Mail;

/**
 * Recomputes an order's completed/remaining/percent from verified
 * publication records and keeps order_status in sync. Call this after any
 * publication is created, updated, imported, or deleted.
 */
class RecalculateSeoOrderProgress
{
    /**
     * These are admin-only decisions; new publication data should never
     * silently move an order out of one of them.
     */
    private const MANUAL_TERMINAL = [
        SeoOrderStatus::Cancelled,
        SeoOrderStatus::RefundRequested,
        SeoOrderStatus::Refunded,
    ];

    public function handle(SeoOrder $order): void
    {
        $order->refresh();

        if (in_array($order->order_status, self::MANUAL_TERMINAL, true)) {
            return;
        }

        $completed = $order->completedCount();
        $recorder = new RecordSeoOrderStatusChange;

        if ($order->quantity > 0 && $completed >= $order->quantity) {
            $wasAlreadyCompleted = $order->order_status === SeoOrderStatus::Completed;
            $recorder->handle($order, SeoOrderStatus::Completed, null, 'Automatically completed — all placements verified.');

            if (! $wasAlreadyCompleted) {
                $order->update(['completed_at' => now()]);
                $this->notify($order->fresh());
            }

            (new GenerateSeoOrderReport)->handle($order->fresh());

            return;
        }

        if ($completed > 0 && $order->order_status !== SeoOrderStatus::PartiallyCompleted) {
            $recorder->handle($order, SeoOrderStatus::PartiallyCompleted, null, 'Automatically updated from verified publication records.');
            $this->notify($order->fresh());
        }
    }

    private function notify(SeoOrder $order): void
    {
        Mail::to($order->user->email)->send(new SeoOrderStatusChanged($order));
    }
}
