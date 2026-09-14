<?php

namespace App\Modules\Shop\Payments\Contracts;

/**
 * Something the customer pays: an order, or a period of a subscription. Payment
 * methods and the payment recorder work on this, not on the concrete models.
 */
interface Payable
{
    /** order | subscription */
    public function payableType(): string;

    public function payableId(): string;

    public function payableDescription(): string;

    /** ['subtotal' => , 'shipping' => , 'discount' => , 'tax' => , 'total' => ] */
    public function payableAmounts(): array;

    /** Lines in the shape of a payment item (name, prd_code, qty, price, subtotal, taxRate, tax, total, deliverable_type…). */
    public function payableItems(): array;

    public function payableUser();

    public function payableCompany();

    public function payableCustomerEmail(): ?string;

    /** The links a payment record keeps: ['order_id' => …] or ['subscription_id' => …] / ['ref_subscription_id' => …]. */
    public function payableLinks(): array;
}
