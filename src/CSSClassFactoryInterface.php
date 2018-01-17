<?php

namespace Nomensa\FormBuilder;

interface CSSClassFactoryInterface
{

    /**
     * @return Instance of ClassBundle loaded with the classes for a row
     */
    public static function rowClassBundle();

    /**
     * @param int $colCount - Number of columns that the containing row is divided into
     *
     * @return Instance of ClassBundle loaded with the classes for a grid column
     */
    public static function colClassBundle($colCount = 1);

}
