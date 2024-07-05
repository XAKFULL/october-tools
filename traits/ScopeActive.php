<?php namespace Xakfull\Tools\Traits;

/**
 * Add active and hidden local scopes
 *
 * Trait ScopeActive
 * @package Xakfull\Tools\Traits
 * @author XAKFULL
 */
trait ScopeActive
{


    /**
     * Return query only with active records
     * @param $query
     */
    public function scopeActive($query)
    {
        $query->whereIsActive(true);
    }

    /**
     * Return query only with hidden records
     *
     * @param $query
     */
    public function scopeHidden($query)
    {
        $query->whereIsActive(false);
    }

    public function activate()
    {
        if (!$this->id)
            throw new \Exception('Save record before activate');

        self::whereId($this->id)->update([
            'is_active' => true
        ]);
    }

    public function deactivate()
    {
        if (!$this->id)
            throw new \Exception('Save record before deactivate');

        self::whereId($this->id)->update([
            'is_active' => false
        ]);
    }
}
