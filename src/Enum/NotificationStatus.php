<?php

namespace App\Enum;

enum NotificationStatus
{
    case SUCCESS;
    case DANGER;
    case WARNING;

    public function getStatus(): string
    {
        return match ($this) {
            NotificationStatus::SUCCESS => 'success',
            NotificationStatus::DANGER => 'danger',
            NotificationStatus::WARNING => 'warning',
        };
    }
}