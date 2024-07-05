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

    public function registerFormWidgets() {
        return [
            'Xakfull\Tools\FormWidgets\Button' => 'button',
        ];
    }

    public function registerMarkupTags() {
        return [
            'functions' => [
                'collect' => function($var){ return collect($var);},
                'dd' => function($var){ dd($var);}
            ]

        ];
    }
}
