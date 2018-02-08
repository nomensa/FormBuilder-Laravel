<?php

namespace Nomensa\FormBuilder;

use Session;

/** A Trait to contain all the helper function for marking up HTML on FormBuilder */
trait MarkerUpper
{

    /**
     * @param $content
     * @param string $tag
     * @param array $attributes
     *
     * @return string
     */
    public static function wrapInTag($content, $tag, $attributes = [])
    {
        return '<' . $tag . ' ' . self::convertAttributesToString($attributes) . '>' . $content . '</' . $tag . '>';
    }


    /**
     * @param array $attributes
     *
     * @return string
     */
    public static function convertAttributesToString($attributes)
    {
        $strAttributes = '';

        foreach ($attributes as $key => $attr) {
            $strAttributes .= ' ' . $key . '="' . $attr . '"';
        }

        return trim($strAttributes);
    }


    /**
     * Converts dot-notation to brackets for use in a HTML input name attribute
     * eg 'rcoa.foo.bar' becomes 'rcoa[foo][bar]'
     *
     * @param $string
     * @return string
     */
    public static function htmlNameAttribute($string)
    {
        $parts = explode('.',$string);
        $string = $parts[0];
        array_shift($parts);
        foreach($parts as $part){
            $string .= '[' . $part . ']';
        }
        return $string;
    }

    public static function HTMLIDFriendly($string)
    {
        return str_replace(['.', ' '], '_', $string);
    }


    public static function HTMLStringDotify($string)
    {
        return str_replace('_', '.', $string);
    }


    public static function makeErrorAnchorName($string)
    {
        return 'error-' . self::HTMLIDFriendly($string);
    }


    /**
     * @param $errors
     * @param string $errorMessageHeader
     * @param array $fieldMap
     *
     * @return string
     */
    public static function formatErrorMessages($errors, $errorMessageHeader,$fieldMap)
    {
        $defaultErrorMessage = config('constants.errors.default');

        $output = '<section id="sectionPageErrors">';
        $output .= !empty($errorMessageHeader) ? "<h2>" . $errorMessageHeader . "</h2>" : "<h2>" . $defaultErrorMessage . "</h2>";

        foreach ($errors->getBag('default')->toArray() as $index => $errorMessages) {

            $errorAnchorName = MarkerUpper::makeErrorAnchorName($index);

            foreach($errorMessages as $error) {
                $error = strip_tags($error);

                if (is_array($fieldMap)) {

                    foreach ($fieldMap as $fieldName => $value) {
                        $pos = strpos($error, $fieldName);
                        if ($pos !== false) {
                            $error = substr_replace($error, $value->label, $pos, strlen($fieldName));
                        };
                    }

                    $error = str_replace('The', '', $error);
                    $error = str_replace('An ', '', $error);
                    $error = str_replace('Assessor', '<strong>Assessor</strong>', $error);
                    $error = str_replace('Unit', '<strong>Unit</strong>', $error);
                }

                $output .= "<p  class=\"flash alert-danger\"><a href=\"#" . $errorAnchorName . "\">" . $error . "</a></p>";

            }
        }

        $output .= "</section>";

        return $output;
    }


    /**
     * @param Illuminate\Support\MessageBag or Illuminate\Support\ViewErrorBag $errors
     * @param $fieldName
     * @param array $fieldMap
     *
     * @return string
     */
    public static function inlineFieldError($errors, $fieldName, $fieldMap)
    {
        if (count($errors) == 0) {
            return false;
        }

        $fields = parent::mapFieldsToLabels($fieldMap);

        $output = '';

        foreach ($errors->get($fieldName) as $errorMessage) {

            $pos = strpos($errorMessage, $fieldName);

            if ($pos !== false) {
                $errorMessage = substr_replace($errorMessage, 'field', $pos, strlen($fieldName));
            }

            $errorMessage = str_replace('An field ', 'An ', $errorMessage);
            $errorMessage = str_replace('The ', 'This ', $errorMessage);

            $errorMessage = str_replace('is 1', 'is Yes', $errorMessage);
            $errorMessage = str_replace('is 2', 'is No', $errorMessage);

            // TODO check error messages work with individual values from radios / checkbox arrays

            $errorMessage = str_replace(array_keys($fields), array_values($fields), $errorMessage);

            $output = '<div data-alert class="alert-box alert">';
            $output .= "<span>" . __($errorMessage) . "</span>";
            $output .= '</div>';
        }

        return $output;
    }


    /**
     * Make an Array of fieldnames with their labels
     * @param $fieldMap
     *
     * @return array
     */
    private static function mapFieldsToLabels($fieldMap){

        $fields = [];

        foreach ($fieldMap as $key => $value){
            $fields[$key] = '<strong>' . $value->label . '</strong>';
        };

        return $fields;
    }



    /**
     * Generates some JavaScript to instruct the page
     *
     * @return null|string
     */
    public function formatDynamicValidationMethods()
    {
        // TODO: set this to work for other access states
        $fields = $this->getRuleGroup('default');

        if (empty($fields)) {
            return '';
        }

        // The JavaScript string which we'll inject
        $js = PHP_EOL;
        $indentation = '            ';

        foreach ($fields as $field => $validationRuleString) {

            $validationRules = explode('|',$validationRuleString);

            foreach ($validationRules as $validationRule) {

                if ($validationRule != 'nullable') {

                    if (strstr($validationRule, 'required_if') == true) {

                        $toggledFieldID = $this->HTMLIDFriendly($field);

                        // The thing after the comma is the value
                        $parts = explode(",", $validationRule);


                        list($ruleName, $valueFieldID) = explode(':',
                          $parts[0]);
                        $valueFieldID = $this->HTMLIDFriendly($valueFieldID);
                        // Pop off the first item
                        array_shift($parts);
                        $showOnValues = implode(',', $parts);

                        $js .= $indentation . "showHideField('#" . $toggledFieldID . "','#" . $valueFieldID . "', '" . $showOnValues . "');" . PHP_EOL;

                    }
                }
            }
        }

        return $js;
    }

}
