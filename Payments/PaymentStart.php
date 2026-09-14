<?php

namespace App\Modules\Shop\Payments;

/** What happens after the customer picks a payment method. */
final class PaymentStart
{
    private function __construct(
        public readonly ?string $url = null,
        public readonly ?string $message = null,
    ) {
    }

    /** Send the customer to a hosted checkout page. */
    public static function redirect(string $url): self
    {
        return new self(url: $url);
    }

    /** Stay on the order page and show instructions (bank transfer, pay later…). */
    public static function message(string $message): self
    {
        return new self(message: $message);
    }
}
