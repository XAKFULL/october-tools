<?php namespace Xakfull\Tools\Classes;

use Backend\Classes\Controller;

/**
 * Class RouteGenerator
 * @package Xakfull\Tools\Classes
 */
class RouteGenerator extends Controller
{
    public static function getClass($dir, $plugin, $file, $folder){
        $method = strtolower(request()->method());
        $index = $method == 'get' ? 'index' : $method.'Index';
        $file = is_null($file) ? $index : $method.ucfirst($file);

        $class = "Xakfull\\".$plugin."\Api\\".(is_null($folder) ? '' : ucfirst($folder).'\\').ucfirst($file);
        try {
            include_once($dir.($folder).'/'.ucfirst($file).'.php');
        } catch (\Exception $exception){
            trace_log($exception);
            return app()->abort(404,'url_not_found : '.$dir.($folder).'/'.ucfirst($file).'.php');
        }

        return (new $class)->apply();
    }

}
