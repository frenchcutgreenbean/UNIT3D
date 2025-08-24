<?php

namespace App\Enums;

enum BetStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * Get human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::CLOSED => 'Closed',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Get CSS class for the status.
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::OPEN => 'badge-primary',
            self::CLOSED => 'badge-warning',
            self::COMPLETED => 'badge-success',
            self::CANCELLED => 'badge-secondary',
        };
    }

    /**
     * Check if the status allows betting.
     */
    public function allowsBetting(): bool
    {
        return $this === self::OPEN;
    }

    /**
     * Check if the status is final (cannot be changed).
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED]);
    }
}
