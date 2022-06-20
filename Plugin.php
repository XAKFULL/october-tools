<?php namespace Xakfull\Tools;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Tools',
            'description' => 'Tools for faster develop',
            'author'      => 'XAKFULL',
            'icon'        => 'icon-pencil',
            'homepage'    => ''
        ];
    }

    public function registerComponents()
    {
        return [];
    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'collect' => function($var){ return collect($var);},
                'dd' => function($var){ dd($var);}
                // A global function, i.e str_plural()
//                'dd' => 'str_plural',

                // A local method, i.e $this->makeTextAllCaps()
//                'uppercase' => [$this, 'makeTextAllCaps']
            ],
            'functions' => [
                'collect' => function($var){ return collect($var);},
                'dd' => function($var){ dd($var);}
            ]
        ];
    }
}
