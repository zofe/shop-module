<?php

namespace App\Modules\Shop\Provisioning;

use App\Modules\Shop\Models\ServiceItem;
use App\Modules\Shop\Provisioning\Contracts\Provisioner;

/**
 * The registered provisioning drivers: config('shop.provisioning.drivers') (name => class)
 * plus 'default' (config('shop.provisioning.default')). A product picks one by name.
 */
class Provisioners
{
    public const DEFAULT = 'default';

    /** name => class */
    public function all(): array
    {
        return array_merge(
            [self::DEFAULT => config('shop.provisioning.default', DefaultProvisioner::class)],
            config('shop.provisioning.drivers', []),
        );
    }

    /** name => label, for a select */
    public function options(): array
    {
        $options = [];
        foreach ($this->all() as $name => $class) {
            $options[$name] = $name === self::DEFAULT ? 'Default (record + licence)' : $name;
        }

        return $options;
    }

    public function has(?string $name): bool
    {
        return $name !== null && isset($this->all()[$name]);
    }

    /** The driver by name; an unknown or empty name falls back to the default one. */
    public function resolve(?string $name): Provisioner
    {
        $class = $this->all()[$this->has($name) ? $name : self::DEFAULT];

        return $class instanceof Provisioner ? $class : app($class);
    }

    /** The driver of a service item: its own name, else the product's, else the default. */
    public function for(ServiceItem $service): Provisioner
    {
        return $this->resolve($service->provisionerName());
    }
}
