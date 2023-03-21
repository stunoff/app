<?php

namespace App\Utils;

class TextUtil
{
    public static function convertToLowerCase($string)
    {
        return mb_strtolower($string, mb_detect_encoding($string));
    }
}
