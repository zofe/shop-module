<?php

namespace App\Modules\Shop\Payments\Recorders;

use App\Modules\Shop\Payments\Contracts\Payable;
use App\Modules\Shop\Payments\Contracts\PaymentRecorder;
use Illuminate\Support\Facades\Event;

/**
 * Payment records in zofe/payments-module: Payment + PaymentItem, linked to the
 * order (order_id) or to the subscription (subscription_id for the first period,
 * ref_subscription_id for the following ones), pending until confirmed.
 */
class PaymentsModuleRecorder implements PaymentRecorder
{
    public function available(): bool
    {
        return class_exists(\App\Modules\Payments\Models\Payment::class);
    }

    public function pending(Payable $payable, array $overrides = []): ?object
    {
        if ($existing = $this->findPending($payable)) {
            return $existing;
        }
        $amounts = $payable->payableAmounts();

        $payment = \App\Modules\Payments\Models\Payment::create(array_merge([
            'description'  => $payable->payableDescription(),
            'payment_type' => $payable->payableType(),
            'status'       => 'pending',
            'gateway'      => null,
            'discount'     => $amounts['discount'] ?? 0,
            'subtotal'     => $amounts['subtotal'] ?? 0,
            'shipping'     => $amounts['shipping'] ?? 0,
            'tax'          => $amounts['tax'] ?? 0,
            'total'        => $amounts['total'] ?? 0,
            'billable_type' => $payable->payableCompany() ? 'company' : ($payable->payableUser() ? 'user' : null),
            'billable_id'   => $payable->payableCompany()?->id ?? $payable->payableUser()?->id,
        ], $payable->payableLinks(), $overrides));

        foreach ($payable->payableItems() as $item) {
            \App\Modules\Payments\Models\PaymentItem::create(array_merge($item, ['payment_id' => $payment->id]));
        }

        return $payment;
    }

    public function findPending(Payable $payable): ?object
    {
        $query = \App\Modules\Payments\Models\Payment::query()->where('status', 'pending');
        foreach ($payable->payableLinks() as $column => $value) {
            $query->where($column, $value);
        }
        if ($payable->payableType() === 'subscription') {
            $query->where('description', $payable->payableDescription());   // one per period
        }

        return $query->orderByDesc('created_at')->first();
    }

    public function confirm(object $payment, string $by = 'manual'): void
    {
        $payment->forceFill([
            'status'       => 'confirmed',
            'payment_date' => $payment->payment_date ?? now(),
            'gateway'      => $payment->gateway ?: $by,
        ])->save();

        if (class_exists(\App\Modules\Payments\Events\PaymentConfirmed::class)) {
            Event::dispatch(new \App\Modules\Payments\Events\PaymentConfirmed($payment->fresh()));
        }
    }

    public function fail(object $payment, ?string $reason = null): void
    {
        $payment->forceFill([
            'status'   => 'failed',
            'metadata' => array_merge($payment->metadata ?? [], array_filter(['failure' => $reason])),
        ])->save();
    }

    public function history(Payable $payable): iterable
    {
        $query = \App\Modules\Payments\Models\Payment::query();
        $links = $payable->payableLinks();
        if ($payable->payableType() === 'subscription') {
            $id = $links['subscription_id'] ?? $links['ref_subscription_id'];
            $query->where(fn ($q) => $q->where('subscription_id', $id)->orWhere('ref_subscription_id', $id));
        } else {
            foreach ($links as $column => $value) {
                $query->where($column, $value);
            }
        }

        return $query->orderByDesc('created_at')->get();
    }
}
