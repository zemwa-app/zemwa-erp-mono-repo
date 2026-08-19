<?php

namespace Modules\Affiliate\Enums;

enum PayoutMethod: string
{
    // phpcs:disable
        case BankAccount = 'bank account';
        case Cash = 'cash';
        case Paypal = 'paypal';
        case Stripe = 'stripe';
        case Other = 'other';
    // phpcs:enable

    // This method is used to display the enum value in the user interface.
    public function label(): string
    {
        return match ($this) {
            self::Cash => __('modules.bankaccount.' . $this->value),
            self::Paypal, self::Stripe, self::Other => __('app.' . $this->value),
            self::BankAccount => __('affiliate::app.'  . $this->value),
            default => $this->value,
        };
    }

}
