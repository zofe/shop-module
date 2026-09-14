<?php

namespace App\Modules\Shop\Payments\Contracts;

/**
 * Keeps the local payment records (the Payment of zofe/payments-module): one is
 * created "pending" when something becomes due, and stays so until a gateway
 * (webhook) or an operator confirms it. Without a payments module the shop runs
 * on its workflows alone (NullRecorder).
 */
interface PaymentRecorder
{
    public function available(): bool;

    /** The pending payment of this payable, or a new one. Returns the record (any object with an id) or null. */
    public function pending(Payable $payable, array $overrides = []): ?object;

    /** The open (pending) payment of this payable, if any. */
    public function findPending(Payable $payable): ?object;

    /** The gateway / the operator confirmed it. */
    public function confirm(object $payment, string $by = 'manual'): void;

    /** The collection failed. */
    public function fail(object $payment, ?string $reason = null): void;

    /** All the records of a payable, newest first. */
    public function history(Payable $payable): iterable;
}
