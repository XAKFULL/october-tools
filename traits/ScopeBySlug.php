<?php namespace Xakfull\Tools\Traits;

use October\Rain\Database\Traits\Sluggable;

/**
 * Add local scope by slug with Rainlab.Translate support
 *
 * Trait ScopeBySlug
 * @package Xakfull\Tools\Traits
 * @author XAKFULL
 */
trait ScopeBySlug
{
    use Sluggable;
    /**
     * Return query with slug=$slug attribute
     *
     * @param $query
     * @param $slug
     */
    public function scopeBySlug($query, $slug)
    {
        if ($this->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel'))
            $query->transWhere('slug', $slug);
        else
            $query->where('slug', $slug);
    }

    public function setUrl($pageName, $controller)
    {
        $params = [
            'id'   => $this->id,
            'slug' => $this->slug
        ];

        return $this->url = $controller->pageUrl($pageName, $params, false);
    }
}
