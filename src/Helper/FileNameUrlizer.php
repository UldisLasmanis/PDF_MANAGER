<?php


namespace App\Helper;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileNameUrlizer extends Urlizer
{
    public static function urlizeFile(UploadedFile $file): string
    {
        return
            Urlizer::urlize($file->getClientOriginalName()) . '-'
            . uniqid() . '.'
            . $file->guessExtension()
        ;
    }

    public static function urlizeImagick(): string
    {
        return Urlizer::urlize(md5(rand())) . '-' . uniqid() . '.jpg';
    }
}