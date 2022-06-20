<?php namespace Xakfull\Tools\Classes;

use Illuminate\Support\Facades\Storage;
use October\Rain\Support\Facades\Str;
use System\Models\File;

class BaseDecoder {
    static function decodeFileToFileUploader(string $data, string $fileName, string $oneSId = null){
        $name = isset(explode('.', $fileName)[0]) ? explode('.', $fileName)[0] : $fileName;
        $fileName = self::slugFileName($fileName);

        if (is_null($fileName))
            return $fileName;

        $file = (new File())->fromData(base64_decode( $data), $fileName);
        $file->title = $name;
        $file->one_s_id = $oneSId;

        return $file;
    }

    static function decodeFileToMedia(string $basePath, string $data,  string $fileName){
        $fileName = self::slugFileName($fileName);

        if (is_null($fileName))
            return $fileName;

        $path = $basePath.self::slugFileName($fileName);

        Storage::put($path, base64_decode($data));

        return str_replace('media/','', $path);
    }

    static function slugFileName($path){
        $parts = explode('.', $path);
        if ($parts[0] == '')
            return null;
        if (count($parts) < 2)
            $parts[] = 'png';
        return Str::slug($parts[0]).'.'.$parts[1];
    }

}
