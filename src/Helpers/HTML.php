<?php

namespace Nomensa\FormBuilder\Helpers;

class HTML {

    /**
     * Encodes unencoded ampersands (does not support HTML Number encoding, only HTML name https://www.ascii.cl/htmlcodes.htm)
     *
     * @param $string
     * @return string
     */
    public static function encodeAmpersands($string)
    {
        return preg_replace('/&(?![A-Za-z0-9]+;)/','&amp;', $string);
    }

}
