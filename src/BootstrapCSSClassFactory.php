<?php

namespace Nomensa\FormBuilder;

class BootstrapCSSClassFactory implements CSSClassFactoryInterface
{

    /**
     * @return \Nomensa\FormBuilder\ClassBundle
     */
    public static function rowClassBundle()
    {
        return new ClassBundle('row form-item-row');
    }

    /**
     * Returns the appropriate Bootstrap CSS class to evenly space columns
     *
     * @param $colCount - Defaults to 1/full width
     *
     * TODO Review for small-screen layout
     *
     * @return \Nomensa\FormBuilder\ClassBundle
     */
    public static function colClassBundle($colCount = 1)
    {
        $xsColWidth = floor(12 / $colCount);
        $smColWidth = floor(12 / $colCount);
        $mdColWidth = floor(12 / $colCount);

        return new ClassBundle([
            'col-xs-' . $xsColWidth,
            'col-sm-' . $smColWidth,
            'col-md-' . $mdColWidth,
        ]);
    }

    /**
     * @return \Nomensa\FormBuilder\ClassBundle
     */
    public static function selectClassBundle()
    {
        return new ClassBundle('form-control');
    }

}
