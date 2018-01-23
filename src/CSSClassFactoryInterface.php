<?php

namespace Nomensa\FormBuilder;

interface CSSClassFactoryInterface
{

    /**
     * @return \Nomensa\FormBuilder\ClassBundle ClassBundle loaded with the classes for a row
     */
    public static function rowClassBundle();

    /**
     * @param int $colCount - Number of columns that the containing row is divided into
     *
     * @return \Nomensa\FormBuilder\ClassBundle loaded with the classes for a grid column
     */
    public static function colClassBundle($colCount = 1);

    /**
     * @return \Nomensa\FormBuilder\ClassBundle loaded with class for a select box input
     */
    public static function selectClassBundle();

}
