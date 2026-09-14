<?php

namespace App\Modules\Shop\Payments\Recorders;

use App\Modules\Shop\Payments\Contracts\Payable;
use App\Modules\Shop\Payments\Contracts\PaymentRecorder;

/** No payments module installed: nothing is recorded, the workflows carry the state. */
class NullRecorder implements PaymentRecorder
{
    public function available(): bool
    {
        return false;
    }

    public function pending(Payable $payable, array $overrides = []): ?object
    {
        return null;
    }

    public function findPending(Payable $payable): ?object
    {
        return null;
    }

    public function confirm(object $payment, string $by = 'manual'): void
    {
    }

    public function fail(object $payment, ?string $reason = null): void
    {
    }

    public function history(Payable $payable): iterable
    {
        return [];
    }
}
