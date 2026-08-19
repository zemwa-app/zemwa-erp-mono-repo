<?php

namespace Modules\Affiliate\Enums;

enum PayoutType: string
{

    // phpcs:disable
    case OnSignUp = 'on signup';
    case AfterSignUp = 'after signup';
    // phpcs:enable

    // This method is used to display the enum value in the user interface.
    public function label(): string
    {
        return match ($this) {
            self::OnSignUp => __('affiliate::app.onSignUp'),
            self::AfterSignUp => __('affiliate::app.afterSignUp'),
            default => $this->value,
        };
    }

}
