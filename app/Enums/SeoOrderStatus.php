<?php

namespace App\Enums;

enum SeoOrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case OrderReceived = 'order_received';
    case InProgress = 'in_progress';
    case PartiallyCompleted = 'partially_completed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case RefundRequested = 'refund_requested';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pending Payment',
            self::Paid => 'Paid',
            self::OrderReceived => 'Order Received',
            self::InProgress => 'In Progress',
            self::PartiallyCompleted => 'Partially Completed',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::RefundRequested => 'Refund Requested',
            self::Refunded => 'Refunded',
        };
    }
}
