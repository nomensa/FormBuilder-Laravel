<?php

use PHPUnit\Framework\TestCase;

use Nomensa\FormBuilder\Helpers\HTML;

class HTMLHelperTest extends TestCase
{


    public function testEncodeAmpersands()
    {
        $originalHtml = 'A&E department, Artist & Revenue, D&amp;D, players enjoy PB&J, N&V, A&amp;E &nbsp; &quot;Hello&quot; &sup2; &Uuml;';

        $expected = 'A&amp;E department, Artist &amp; Revenue, D&amp;D, players enjoy PB&amp;J, N&amp;V, A&amp;E &nbsp; &quot;Hello&quot; &sup2; &Uuml;';

        $this->assertEquals($expected, HTML::encodeAmpersands($originalHtml));
    }


    public function testDoesNotDoubleEncode()
    {
        $originalHtml = 'A&amp;E';

        $expected = 'A&amp;E';

        $this->assertEquals($expected, HTML::encodeAmpersands($originalHtml));
    }


}
