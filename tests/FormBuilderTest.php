<?php

use PHPUnit\Framework\TestCase;

use Nomensa\FormBuilder\FormBuilder;
use Nomensa\FormBuilder\Exceptions\InvalidSchemaException;

class FormBuilderTest extends TestCase {

    public function testHtmlNameAttribute()
    {
        $this->assertEquals(FormBuilder::htmlNameAttribute('rcoa.foo.bar'),'rcoa[foo][bar]');
    }

    public function testMissingColumnFieldValueCausesInvalidSchemaException()
    {
        $this->expectException(InvalidSchemaException::class);
        $this->expectExceptionMessage('Columns must have a "field" value');

        $jsonSchema = '[
            {
                "type": "dynamic",
                "rows": [
                  {
                    "columns": [
                      { 
                        
                      }
                    ]
                  }
                ]
             }]';
        $schema = json_decode($jsonSchema, true);

        $options = json_decode('{}', false);

        new FormBuilder($schema, $options);
    }

    public function testMissingColumnLabelValueCausesInvalidSchemaException()
    {
        $this->expectException(InvalidSchemaException::class);
        $this->expectExceptionMessage('Columns must have a "label" value');

        $jsonSchema = '[
            {
                "type": "dynamic",
                "rows": [
                  {
                    "columns": [
                      { 
                        "field": "field-1"
                      }
                    ]
                  }
                ]
             }]';
        $schema = json_decode($jsonSchema, true);

        $options = json_decode('{}', false);

        new FormBuilder($schema, $options);
    }

    public function testMissingColumnTypeValueCausesInvalidSchemaException()
    {
        $this->expectException(InvalidSchemaException::class);
        $this->expectExceptionMessage('Columns must have a "type" value');

        $jsonSchema = '[
            {
                "type": "dynamic",
                "rows": [
                  {
                    "columns": [
                      { 
                        "field": "field-1",
                        "label": "Field One"
                      }
                    ]
                  }
                ]
             }]';
        $schema = json_decode($jsonSchema, true);

        $options = json_decode('{}', false);

        new FormBuilder($schema, $options);
    }

    /**
     * Creates a valid instance of FormBuilder for use in several tests below
     *
     * @return FormBuilder
     */
    private function makeTestFormBuilder()
    {
        $jsonSchema = '[
            {
                "type": "dynamic",
                "rows": [
                  {
                    "columns": [
                      { 
                        "field": "field-1",
                        "label": "Field One",
                        "type": "text"
                      }
                    ]
                  }
                ]
             }]';
        $schema = json_decode($jsonSchema, true);

        $jsonOptions = '{
                "rules": {
                    "draft": {},
                    "default": {
                        "field-1": "nullable",
                        "field-2": "required",
                        "field-3": "max:255|required",
                        "field-4": "required_if:field-7,1"
                    }
                }
            }';

        $options = json_decode($jsonOptions, false);

        return new FormBuilder($schema, $options);
    }

    public function testRuleExistsTrue1()
    {
        $formBuilder = $this->makeTestFormBuilder();
        $this->assertTrue($formBuilder->ruleExists("field-1","nullable"));
    }

    public function testRuleExistsTrue2()
    {
        $formBuilder = $this->makeTestFormBuilder();
        $this->assertTrue($formBuilder->ruleExists("field-3","required"));
    }

    public function testRuleExistsFalse1()
    {
        $formBuilder = $this->makeTestFormBuilder();
        $this->assertFalse($formBuilder->ruleExists("field","nullable"));
    }

    public function testRuleExistsFalse2()
    {
        $formBuilder = $this->makeTestFormBuilder();
        $this->assertFalse($formBuilder->ruleExists("field-1","required"));
    }

    public function testRuleExistsFalse3()
    {
        $formBuilder = $this->makeTestFormBuilder();
        $this->assertFalse($formBuilder->ruleExists("field-4","required"));
    }

}
