<?php

namespace Modules\Affiliate\Enums;

enum PayoutTime: string
{

    // phpcs:disable
    case OneTime = 'one time';
    case EveryTime = 'every time';
    // phpcs:enable

    // This method is used to display the enum value in the user interface.
    public function label(): string
    {
        return match ($this) {
            self::OneTime => __('affiliate::app.oneTime' ),
            self::EveryTime => __('affiliate::app.everyTime'),
            default => $this->value,
        };
    }

}
