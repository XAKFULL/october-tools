<?php namespace Xakfull\Tools\Classes;

use Flash;
use Log;
use Cms\Classes\Page;
use Illuminate\Support\Facades\Redirect;

/**
 * Class ResponseGenerator
 * @package Xakfull\Tools\Classes
 */
class ResponseGenerator
{

    /**
     * Return response with flash message and log
     * @param $text
     * @param int $code
     * @param false $state
     * @return \Illuminate\Http\Response
     */
    static function withFlash($text, $code = 200, $state = false){

        if (!$state)
            $state = $code == 200 ? 'success' : 'error';

        if ($code != 200)
            Log::error($text, ['code' => $code]);

        Flash::$state($text);

        return response($text, $code);
    }

    /**
     * Return redirect with flash message and log
     * @param $page
     * @param $text
     * @param int $code
     * @param false $state
     * @return \Illuminate\Http\Response
     */
    static function redirectWithFlash($page, $text, $code = 301, $state = false){

        if (!$state)
            $state = $code == 200 ? 'success' : 'error';

        if ($code != 301)
            Log::error($text, ['code' => $code]);

        Flash::$state($text);

        return Redirect::to(Page::url($page), 301);
    }

    static function withFlashData($text, $data){
        Flash::success($text);
        return $data;
    }


}
