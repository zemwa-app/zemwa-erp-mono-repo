<?php

namespace Modules\Affiliate\Enums;

enum CommissionType: string
{

    // phpcs:disable
    case Fixed = 'fixed';
    case Percent = 'percent';
    // phpcs:enable

    // This method is used to display the enum value in the user interface.
    public function label(): string
    {
        return match ($this) {
            self::Fixed => __('affiliate::app.' . $this->value),
            self::Percent => __('affiliate::app.' . $this->value),
            default => $this->value,
        };
    }

}
