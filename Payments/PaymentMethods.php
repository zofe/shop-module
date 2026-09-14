<?php

namespace App\Modules\Shop\Payments;

use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Payments\Contracts\PaymentMethod;
use Illuminate\Support\Collection;

/** The registry of the payment methods: config('shop.payment_methods') plus what modules register. */
class PaymentMethods
{
    /** @var array<string, PaymentMethod|string> key => instance or class */
    protected array $methods = [];

    public function __construct()
    {
        foreach (config('shop.payment_methods', []) as $method) {
            $this->register($method);
        }
    }

    public function register(PaymentMethod|string $method): static
    {
        $instance = is_string($method) ? app($method) : $method;
        $this->methods[$instance->key()] = $instance;

        return $this;
    }

    public function forget(string $key): static
    {
        unset($this->methods[$key]);

        return $this;
    }

    /** @return Collection<string, PaymentMethod> */
    public function all(): Collection
    {
        return collect($this->methods);
    }

    /** @return Collection<string, PaymentMethod> the ones to offer for this order */
    public function for(Order $order): Collection
    {
        return $this->all()->filter(fn (PaymentMethod $m) => $m->available($order));
    }

    public function find(string $key): ?PaymentMethod
    {
        return $this->methods[$key] ?? null;
    }
}
