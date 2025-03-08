<?php

namespace App\Modules\Shop\Cart\Contracts;

interface Buyable
{
    /**
     * Get the identifier of the Buyable item.
     *
     * @return int|string
     */
    public function getBuyableIdentifier($options = null);


    /**
     * Get the sku or title of the Buyable item.
     *
     * @return string
     */
    public function getBuyableSku($options = null);

    /**
     * Get the description or title of the Buyable item.
     *
     * @return string
     */
    public function getBuyableDescription($options = null);

    /**
     * Get the price of the Buyable item.
     *
     * @return float
     */
    public function getBuyablePrice($options = null);

    /**
     * Get the activation price of the Buyable item/service.
     *
     * @return float
     */
    public function getBuyablePriceActivation($options = null);

    /**
     * Get the weight of the Buyable item.
     *
     * @return float
     */
    public function getBuyableWeight($options = null);

    /**
     * Get the shipping cost of the Buyable item.
     *
     * @return float
     */
    public function getBuyableShipping($options = null);
}
