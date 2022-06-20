<?php namespace Xakfull\Tools\Traits;

use Flash;
use Log;

/**
 * Trait FloatPriceAttribute
 * @package Xakfull\Tools\Traits
 */
trait FloatPriceAttribute
{

    /**
     * Save float price as int
     * @param $value
     */
    public function setPriceAttribute($value)
    {
        $newValue = explode('.', $value);
        if (count($newValue) > 1) {
            $newValue[1] = strlen((string)$newValue[1]) > 2 ? substr((string)$newValue[1], 0, 2) : $newValue[1] * 10;
            $this->attributes['price'] = (int)implode($newValue);
        } else {
            $this->attributes['price'] = $value * 100;
        };
    }

    /**
     * Get float price from int
     * @param $value
     * @return float|int
     */
    public function getPriceAttribute($value)
    {
        return $value / 100;
    }
}
