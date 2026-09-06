<?php

namespace App\Enums;

enum PublicationStatus: string
{
    case Pending = 'pending';
    case Submitted = 'submitted';
    case Published = 'published';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case ReplacementRequired = 'replacement_required';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Submitted => 'Submitted',
            self::Published => 'Published',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
            self::ReplacementRequired => 'Replacement Required',
        };
    }

    /**
     * Only verified placements count toward a customer's completed quantity.
     */
    public function countsAsCompleted(): bool
    {
        return $this === self::Verified;
    }
}
