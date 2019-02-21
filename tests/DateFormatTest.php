<?php

use PHPUnit\Framework\TestCase;

use Nomensa\FormBuilder\Helpers\DateStringHelper;

class DateStringHelperTest extends TestCase
{


    public function testSpaceSeparatedReverseOrderDoubleDigits()
    {
        $result = DateStringHelper::formatIfDateString('05 04 2018');
        $this->assertEquals('2018-04-05', $result);
    }


    public function testSpaceSeparatedReverseOrderSingleDigitMonth()
    {
        $result = DateStringHelper::formatIfDateString('05 4 2018');
        $this->assertEquals('2018-04-05', $result);
    }


    public function testSpaceSeparatedReverseOrderSingleDigitDay()
    {
        $result = DateStringHelper::formatIfDateString('5 04 2018');
        $this->assertEquals('2018-04-05', $result);
    }


    public function testSpaceSeparatedDoubleDigits()
    {
        $result = DateStringHelper::formatIfDateString('2018 04 05');
        $this->assertEquals('2018-04-05', $result);
    }


    public function testSpaceSeparatedSingleDigitMonth()
    {
        $result = DateStringHelper::formatIfDateString('2018 4 05');
        $this->assertEquals('2018-04-05', $result);
    }


    public function testSpaceSeparatedSingleDigitDay()
    {
        $result = DateStringHelper::formatIfDateString('2018 04 5');
        $this->assertEquals('2018-04-05', $result);
    }


    public function testDashSeparatedSingleDigitDay()
    {
        $result = DateStringHelper::formatIfDateString('2018-04-5');
        $this->assertEquals('2018-04-05', $result);
    }


    public function testDashSeparatedSingleDigitMonth()
    {
        $result = DateStringHelper::formatIfDateString('2018-4-05');
        $this->assertEquals('2018-04-05', $result);
    }


    public function testDateWithWhiteSpace()
    {
        $result = DateStringHelper::formatIfDateString('  2018  4 05  ');
        $this->assertEquals('2018-04-05', $result);
    }


    public function testNotADate()
    {
        $result = DateStringHelper::formatIfDateString('Badger');
        $this->assertNull($result);
    }

}
