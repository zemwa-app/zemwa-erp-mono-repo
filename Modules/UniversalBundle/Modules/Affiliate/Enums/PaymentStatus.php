<?php

namespace Modules\Affiliate\Enums;

enum PaymentStatus: string
{
    // phpcs:disable
    case Paid = 'paid';
    case Pending = 'pending';
    case Canceled = 'canceled';
    // phpcs:enable

    // This method is used to display the enum value in the user interface.
    public function label(): string
    {
        return match ($this) {
            self::Paid, self::Canceled, self::Pending, => __('app.' . $this->value),
            default => $this->value,
        };
    }

    public function html(): string
    {
        return match ($this) {
            self::Paid => "<i class='fa fa-circle mr-2 text-light-green'></i> " . $this->label(),
            self::Pending => "<i class='fa fa-circle mr-2 text-yellow'></i> " . $this->label(),
            self::Canceled => "<i class='fa fa-circle mr-2 text-danger'></i> " . $this->label(),
            default => "<i class='fa fa-circle mr-2 text-info'></i> " . $this->label(),
        };
    }

}
