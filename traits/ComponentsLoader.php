<?php namespace Xakfull\Tools\Traits;

use Xakfull\Shop\Components\Categories;

/**
 * Trait ComponentsLoader
 * @package Xakfull\Tools\Classes
 * @author XAKFULL
 */
trait ComponentsLoader{
    public function registerComponents() {
        $basePath = str_replace('Plugin','Components',get_called_class());
        $basePath = str_replace('\\','/',$basePath);
        $basePath = strtolower($basePath);

        return collect(scandir(plugins_path($basePath)))
            ->filter(function ($item, $key) {
                $parts = explode('.', $item);
                return (count($parts) == 2 and $parts[1] == 'php');
            })
            ->mapWithKeys(function ($item) use ($basePath) {
                $fileName = explode('.', $item)[0];
                $class = str_replace('\\', '/', $basePath.'/'.$fileName);
                $newClass = [];
                foreach (explode('/', $class) as $key => $part){
                    $newClass[] = mb_strtoupper(mb_substr($part, 0, 1)). mb_substr($part, 1);
                }
                return [implode('\\', $newClass) => $fileName];
            })
            ->toArray();
    }

}
