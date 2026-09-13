<?php

namespace App\Modules\Shop\Cart;

use App\Modules\Shop\Cart\Contracts\Buyable;
use App\Modules\Shop\Cart\Contracts\BuyableItem;
use App\Modules\Shop\Cart\Contracts\Calculator;
use App\Modules\Shop\Cart\Exceptions\InvalidCalculatorException;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use ReflectionClass;

/**
 * @property-read mixed discount
 * @property-read float discountTotal
 * @property-read float shipping
 * @property-read float shippingTotal
 * @property-read float priceTarget
 * @property-read float priceNet
 * @property-read float priceTotal
 * @property-read float priceActivation
 *
 * @property-read float priceTotalHw
 * @property-read float priceTotalActivation
 * @property-read float subtotalHw
 * @property-read float subtotalActivation
 *
 * @property-read float subtotal
 * @property-read float taxTotal
 * @property-read float tax
 * @property-read float total
 * @property-read float priceTax
 */
class CartItem implements Arrayable, Jsonable, BuyableItem
{
    /**
     * The rowID of the cart item.
     *
     * @var string
     */
    public $rowId;

    /**
     * The ID of the cart item.
     *
     * @var int|string
     */
    public $id;

    /**
     * The quantity for this cart item.
     *
     * @var int|float
     */
    public $qty;

    /**
     * The sku of the cart item.
     *
     * @var string
     */
    public $sku;

    /**
     * The name of the cart item.
     *
     * @var string
     */
    public $name;

    /**
     * The price without TAX of the cart item.
     *
     * @var float
     */
    public $price;

    /**
     * The activation price for service (to be added to the cart item price).
     *
     * @var float
     */
    public $priceActivation;

    /**
     * The weight of the product.
     *
     * @var float
     */
    public $weight;

    /**
     * The shipping cost of the product.
     *
     * @var float
     */
    public $shipping;

    /**
     * The options for this cart item.
     *
     * @var array
     */
    public $options;

    /**
     * The tax rate for the cart item.
     *
     * @var int|float
     */
    public $taxRate = 0;

    /**
     * The FQN of the associated model.
     *
     * @var string|null
     */
    private $associatedModel = null;

    /**
     * The discount rate for the cart item.
     *
     * @var float
     */
    private $discountRate = 0;



    /**
     * CartItem constructor.
     *
     * @param int|string $id
     * @param string     $name
     * @param float      $price
     * @param float      $priceActivation
     * @param float      $weight
     * @param float      $shipping
     * @param array      $options
     */
    public function __construct($id, $sku, $name, $price, $priceActivation=0, $weight = 0, $shipping = 0, array $options = [])
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Please supply a valid identifier.');
        }
        if (empty($sku)) {
            throw new \InvalidArgumentException('Please supply a valid sku.');
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('Please supply a valid name.');
        }
        if (strlen($price) < 0 || !is_numeric($price)) {
            throw new \InvalidArgumentException('Please supply a valid price.');
        }
        if (strlen($priceActivation) < 0 || !is_numeric($priceActivation)) {
            $priceActivation = 0;
            //throw new \InvalidArgumentException('Please supply a valid activation price.');
        }
        if (strlen($weight) < 0 || !is_numeric($weight)) {
            throw new \InvalidArgumentException('Please supply a valid weight.');
        }
        if (strlen($shipping) < 0 || !is_numeric($shipping)) {
            throw new \InvalidArgumentException('Please supply a shipping price.');
        }

        $this->id = $id;
        $this->sku = $sku;
        $this->name = $name;
        $this->price = floatval($price);
        $this->priceActivation = floatval($priceActivation);
        $this->weight = floatval($weight);
        $this->shipping = floatval($shipping);
        $this->options = new CartItemOptions($options);
        $this->rowId = $this->generateRowId($id, $options);
    }

    /**
     * Returns the formatted weight.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function weight($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->weight, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted price without TAX.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function price($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->price, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * return the formatted activation price without TAX.
     *
     * @param null $decimals
     * @param null $decimalPoint
     * @param null $thousandSeperator
     * @return string
     */
    public function priceActivation($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->priceActivation, $decimals, $decimalPoint, $thousandSeperator);
    }


    /**
     * Returns the formatted price with discount applied.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function priceTarget($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->priceTarget, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted price with TAX.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function priceTax($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->priceTax, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted subtotal.
     * Subtotal is price for whole CartItem without TAX.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function subtotal($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->subtotal, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted total.
     * Total is price for whole CartItem with TAX.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function total($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->total, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted tax.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function tax($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->tax, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted tax.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function taxTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->taxTotal, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted discount.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function discount($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->discount, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted total discount for this cart item.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function discountTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->discountTotal, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted shipping cost for single item.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function shipping($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->shipping, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted total shipping cost * qty.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function shippingTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->shippingTotal, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted total price for this cart item.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function priceTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->priceTotal, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Set the quantity for this cart item.
     *
     * @param int|float $qty
     */
    public function setQuantity($qty)
    {
        if (empty($qty) || !is_numeric($qty)) {
            throw new \InvalidArgumentException('Please supply a valid quantity.');
        }

        $this->qty = $qty;
    }

    /**
     * Update the cart item from a Buyable.
     *
     * @param \App\Modules\Shop\Cart\Contracts\Buyable $item
     *
     * @return void
     */
    public function updateFromBuyable(Buyable $item)
    {
        $this->id = $item->getBuyableIdentifier($this->options);
        $this->name = $item->getBuyableDescription($this->options);
        $this->price = $item->getBuyablePrice($this->options);
        $this->priceActivation = $item->getBuyablePriceActivation($this->options);
        $this->shipping = $item->getBuyableShipping($this->options);
    }

    /**
     * Update the cart item from an array.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function updateFromArray(array $attributes)
    {
        $this->id = Arr::get($attributes, 'id', $this->id);
        $this->sku = Arr::get($attributes, 'sku', $this->sku);
        $this->qty = Arr::get($attributes, 'qty', $this->qty);
        $this->name = Arr::get($attributes, 'name', $this->name);
        $this->price = Arr::get($attributes, 'price', $this->price);
        $this->priceActivation = Arr::get($attributes, 'priceActivation', $this->priceActivation);
        $this->weight = Arr::get($attributes, 'weight', $this->weight);
        $this->shipping = Arr::get($attributes, 'shipping', $this->shipping);
        $this->options = new CartItemOptions(Arr::get($attributes, 'options', $this->options));

        $this->rowId = $this->generateRowId($this->id, $this->options->all());
    }

    /**
     * Associate the cart item with the given model.
     *
     * @param mixed $model
     *
     * @return \App\Modules\Shop\Cart\CartItem
     */
    public function associate($model)
    {
        $this->associatedModel = is_string($model) ? $model : get_class($model);

        return $this;
    }

    /**
     * Set the tax rate.
     *
     * @param int|float $taxRate
     *
     * @return \App\Modules\Shop\Cart\CartItem
 */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * Set the discount rate.
     *
     * @param int|float $discountRate
     *
     * @return \App\Modules\Shop\Cart\CartItem
 */
    public function setDiscountRate($discountRate)
    {
        $this->discountRate = $discountRate;

        return $this;
    }

    /**
     * Get an attribute from the cart item or get the associated model.
     *
     * @param string $attribute
     *
     * @return mixed
     */
    public function __get($attribute)
    {
        if (property_exists($this, $attribute)) {
            return $this->{$attribute};
        }
        $decimals = config('shop.format.decimals', 2);

        switch ($attribute) {
            case 'model':
                return isset($this->associatedModel) ? with(new $this->associatedModel())->find($this->id) : null;

            case 'modelFQCN':
                return $this->associatedModel;

            case 'weightTotal':
                return round($this->weight * $this->qty, $decimals);

            case 'shippingTotal':
                return round($this->shipping * $this->qty, $decimals);

            case 'sku':
                return $this->getSku();
        }

        $class = new ReflectionClass(config('shop.calculator', DefaultCalculator::class));
        if (!$class->implementsInterface(Calculator::class)) {
            throw new InvalidCalculatorException('The configured Calculator seems to be invalid. Calculators have to implement the Calculator Contract.');
        }

        return call_user_func($class->getName().'::getAttribute', $attribute, $this);
    }

    /**
     * Create a new instance from a Buyable.
     *
     * @param App\Modules\Shop\Contracts\Buyable $item
     * @param array $options
     *
     * @return \App\Modules\Shop\Cart\CartItem
 */
    public static function fromBuyable(Buyable $item, array $options = [])
    {
        return new self($item->getBuyableIdentifier($options), $item->getBuyableSku($options), $item->getBuyableDescription($options), $item->getBuyablePrice($options), $item->getBuyablePriceActivation($options), $item->getBuyableWeight($options), $item->getBuyableShipping($options), $options);
    }

    /**
     * Create a new instance from the given array.
     *
     * @param array $attributes
     *
     * @return \App\Cart\CartItem
     */
    public static function fromArray(array $attributes)
    {
        $options = Arr::get($attributes, 'options', []);

        $item = new self(
            $attributes['id'],
            $attributes['sku'] ?? null,
            $attributes['name'] ?? null,
            $attributes['price'] ?? 0,
            $attributes['priceActivation'] ?? 0,
            $attributes['weight'] ?? 0,
            $attributes['shipping'] ?? 0,
            is_array($options) ? $options : []
        );
        $item->qty = $attributes['qty'] ?? 1;
        $item->setTaxRate($attributes['taxRate'] ?? 0);
        $item->setDiscountRate($attributes['discountRate'] ?? 0);
        if (! empty($attributes['associatedModel'])) {
            $item->associate($attributes['associatedModel']);
        }

        return $item;
    }

    /**
     * Create a new instance from the given attributes.
     *
     * @param int|string $id
     * @param string     $sku
     * @param string     $name
     * @param float      $price
     * @param float      $priceActivation
     * @param float      $weight
     * @param float      $shipping
     * @param array      $options
     *
     * @return \App\Cart\CartItem
     */
    public static function fromAttributes($id, $sku, $name, $price, $priceActivation, $weight, $shipping, array $options = [])
    {
        return new self($id, $sku, $name, $price, $priceActivation, $weight, $shipping, $options);
    }

    /**
     * Generate a unique id for the cart item.
     *
     * @param string $id
     * @param array  $options
     *
     * @return string
     */
    protected function generateRowId($id, array $options)
    {
        ksort($options);

        return md5($id.serialize($options));
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'rowId'    => $this->rowId,
            'id'       => $this->id,
            'sku'       => $this->sku,
            'name'     => $this->name,
            'qty'      => $this->qty,
            'price'    => $this->price,
            'priceActivation'    => $this->priceActivation,
            'weight'   => $this->weight,
            'options'  => $this->options->toArray(),
            'discount' => $this->discount,
            'shipping' => $this->shipping,
            'tax'      => $this->tax,
            'subtotal' => $this->subtotal,
            // what fromArray() needs to rebuild the item (the session may store it as JSON)
            'taxRate'         => $this->taxRate,
            'discountRate'    => $this->discountRate,
            'associatedModel' => $this->associatedModel,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get the formatted number.
     *
     * @param float  $value
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    private function numberFormat($value, $decimals, $decimalPoint, $thousandSeperator)
    {
        if (is_null($decimals)) {
            $decimals = config('shop.format.decimals', 2);
        }

        if (is_null($decimalPoint)) {
            $decimalPoint = config('shop.format.decimal_point', '.');
        }

        if (is_null($thousandSeperator)) {
            $thousandSeperator = config('shop.format.thousand_separator', ',');
        }

        return number_format($value, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Getter for the raw internal discount rate.
     * Should be used in calculators.
     *
     * @return float
     */
    public function getDiscountRate()
    {
        return $this->discountRate;
    }

    public function getSku()
    {

        return $this->sku;
    }
}
