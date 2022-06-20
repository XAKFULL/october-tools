<?php namespace Xakfull\Tools\Traits;

/**
 * Add active and hidden local scopes
 *
 * Trait ScopeHidden
 * @package Xakfull\Tools\Traits
 * @author XAKFULL
 */
trait ScopeHidden
{


    /**
     * Return query with hidden=false attribute
     * @param $query
     */
    public function scopeActive($query)
    {
        $query->whereHidden(false);
    }

    /**
     * Return query with hidden=$state attribute
     *
     * @param $query
     * @param false $state
     */
    public function scopeHidden($query, $state = false)
    {
        $query->whereHidden($state);
    }
}
