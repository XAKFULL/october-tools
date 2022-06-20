<?php namespace Xakfull\Tools\Behaviors;

use Db;
use DbDongle;
use October\Rain\Extension\ExtensionBase;
use Str;

/**
 * Float Integer model extension
 *
 * Usage:
 *
 * In the model class definition:
 *
 *   public $implement = ['@Xakfull.Tools.Behaviors.FloatIntegerModel'];
 *
 *   public $floatInt = [
 *      'price',
 *      'weight' => [ 'precision' = 3 ]
 *  ];
 *
 */
class FloatIntegerModel extends ExtensionBase
{
    public function __construct($model)
    {
        $this->model = $model;

        $this->model->bindEvent('model.beforeGetAttribute', function ($key) use ($model) {
            if ($this->isFloatInt($key)) {
                $value = $this->getAttributeFloated($key);
                if ($model->hasGetMutator($key)) {
                    $method = 'get' . Str::studly($key) . 'Attribute';
                    $value = $model->{$method}($value);
                }
                return $value;
            }
        });

        $this->model->bindEvent('model.beforeSetAttribute', function ($key, $value) use ($model) {
            if ($this->isFloatInt($key)) {
                $value = $this->setAttributeIntegered($key, $value);
                if ($model->hasSetMutator($key)) {
                    $method = 'set' . Str::studly($key) . 'Attribute';
                    $value = $model->{$method}($value);
                }
                return $value;
            }
        });
    }

    /**
     * Checks if an attribute should be integered or not.
     * @param  string  $key
     * @return boolean
     */
    public function isFloatInt($key)
    {
        return in_array($key, $this->model->getFloatIntAttributes());
    }
    /**
     * Returns a default attribute precision.
     * @param  string  $key
     * @return integer
     */
    public function getAttributePrecision($key)
    {
        $attributesWithOptions = $this->getFloatIntAttributesWithOptions();

        if (isset($attributesWithOptions[$key]) and isset($attributesWithOptions[$key]['precision']))
            $precision = (int)$attributesWithOptions[$key]['precision'];
        else
            return 2;
    }
    /**
     * Returns a collection of fields that will be hashed.
     * @return array
     */
    public function getFloatIntAttributes()
    {
        $floatInt = [];

        if (!is_array($this->model->floatInt)) {
            return [];
        }

        foreach ($this->model->floatInt as $attribute) {
            $floatInt[] = is_array($attribute) ? array_shift($attribute) : $attribute;
        }

        return $floatInt;
    }
    /**
     * Returns the defined options for a floated integer attribute.
     * @return array
     */
    public function getFloatIntAttributesWithOptions()
    {
        $attributes = [];

        foreach ($this->model->floatInt as $options) {
            if (!is_array($options)) {
                continue;
            }

            $attributeName = array_shift($options);

            $attributes[$attributeName] = $options;
        }

        return $attributes;
    }
    /**
     * Sets a floated integer attribute value.
     * @param  string $key   Attribute
     * @param  float|int  $value Floated Value
     * @return integer       Integered value
     */
    public function setAttributeIntegered($key, $value)
    {
        if (!in_array($key, $this->getFloatIntAttributes()))
            return $value;

        return $this->setAttributeFromData($key, $value);
    }


    /**
     * Save float price as int
     * @param string    $key
     * @param float|int $value
     */
    public function setAttributeFromData($key, $value)
    {
        $precision = $this->getAttributePrecision($key);

        if (is_float($value) and str_contains((string) $value, '.') and strlen(explode('.', (string)$value)[1]) > $precision)
            $value = round($value, $precision,PHP_ROUND_HALF_UP);

        return (int)($value * pow(10, $precision));
    }

    /**
     * Get float price from int
     * @param $value
     * @return float|int
     */
    public function getAttributeFloated($key)
    {
        $precision = $this->getAttributePrecision($key);
        if (isset($this->model->attributes[$key]))
            return $this->model->attributes[$key] / pow(10, $precision);
        else
            return 0;
    }
}
