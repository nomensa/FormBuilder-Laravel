<?php

use PHPUnit\Framework\TestCase;

use Nomensa\FormBuilder\FormBuilder;

class FormBuilderTest extends TestCase {

    public function testHtmlNameAttribute()
    {
        return $this->assertEquals(FormBuilder::htmlNameAttribute('rcoa.foo.bar'),'rcoa[foo][bar]');
    }

}
