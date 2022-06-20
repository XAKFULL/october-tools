<?php namespace Xakfull\Tools\Classes;

/**
 * Class InputFilters
 * @package Xakfull\Tools\Classes
 * @author XAKFULL
 */
class InputFilters{
    static function filterPhone($phone){
        $phone = preg_replace("/[^0-9\.]/", '', $phone);

        if (strlen($phone) <= 10 or strlen($phone) > 11)
            return;

        if ( strlen($phone) == 11 )
            return '8'.substr($phone, 1, strlen($phone));

        return '';
    }
}
