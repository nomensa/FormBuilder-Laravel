<?php

namespace Nomensa\FormBuilder;

class MarkUp
{
    const NO_VISIBLE_CONTENT = false;
    const HAS_VISIBLE_CONTENT = true;

    /**  \Illuminate\Support\HtmlString|string */
    public $html = '';

    /** @var bool */
    public $hasVisibleContent = true;

    public function __construct($html,$hasVisibleContent=true)
    {
        $this->html = $html;
        $this->hasVisibleContent = $hasVisibleContent;
    }

    public function __toString()
    {
        return (string)$this->html;
    }
}
