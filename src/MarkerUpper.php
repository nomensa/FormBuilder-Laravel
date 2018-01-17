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
     * TODO PHPUnit test for this
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
     *
     * @return string
     */
    public static function formatErrorMessages($errors, $errorMessageHeader)
    {
        $defaultErrorMessage = config('constants.errors.default');
        $fields = Session::get('fields');

        $output = '<section id="sectionPageErrors">';
        $output .= !empty($errorMessageHeader) ? "<h2>" . $errorMessageHeader . "</h2>" : "<h2>" . $defaultErrorMessage . "</h2>";

        foreach ($errors->getBag('default')->toArray() as $index => $errorMessages) {

            $errorAnchorName = MarkerUpper::makeErrorAnchorName($index);

            foreach($errorMessages as $error) {
                $error = strip_tags($error);

                $error = str_replace('.', '_', $error);

                if (is_array($fields)) {

                    foreach ($fields as $fieldName => $value) {
                        $pos = strpos($error, $fieldName);
                        if ($pos !== false) {
                            $error = substr_replace($error, $value, $pos, strlen($fieldName));
                        };
                    }

                    $error = str_replace('_', '.', $error);
                    $error = str_replace('The', '', $error);
                    $error = str_replace('An ', '', $error);
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
     *
     * @return string
     */
    public static function inlineFieldError($errors, $fieldName)
    {
        if (count($errors) == 0) {
            return false;
        }

        $output = '';
        $fieldKeys =[];
        $fields = Session::get('fields');

        if (!empty($fields)) {
            $fieldKeys = array_keys($fields);
            $fieldValues = array_values($fields);

            foreach ($fieldKeys as $index => $fieldKey) {
                $fieldKeys[$index] = MarkerUpper::HTMLStringDotify($fieldKey);
            }
        }


        foreach ($errors->get($fieldName) as $errorMessage) {

            $pos = strpos($errorMessage, $fieldName);
            if ($pos !== false) {
                $errorMessage = substr_replace($errorMessage, 'field', $pos, strlen($fieldName));
            }

            $errorMessage = str_replace('An field', 'An', $errorMessage);
            $errorMessage = str_replace('The', 'This', $errorMessage);

            if (!empty($fields) and isset($fieldKeys) and isset($fieldValues)) {
                $errorMessage = str_replace('is 1', 'is Yes', $errorMessage);
                $errorMessage = str_replace('is 2', 'is No', $errorMessage);
            }

            $errorMessage = str_replace($fieldKeys, $fieldValues, $errorMessage);

            $output = '<div data-alert class="alert-box alert">';
            $output .= "<span>" . __($errorMessage) . "</span>";
            $output .= '</div>';
        }

        return $output;
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

        foreach ($fields as $field => $validationRules) {

            if ($validationRules != 'nullable') {

                if (strstr($validationRules, 'required_if') == true) {

                    $toggledFieldID = $this->HTMLIDFriendly($field);

                    // The thing after the comma is the value
                    $parts = explode(",", $validationRules);


                    list($ruleName,$valueFieldID) = explode(':',$parts[0]);
                    $valueFieldID = $this->HTMLIDFriendly($valueFieldID);
                    // Pop off the first item
                    array_shift($parts);
                    $showOnValues = implode(',', $parts);

                    $js .= $indentation . "showHideTextArea('#" . $toggledFieldID . "','#" . $valueFieldID . "', '" . $showOnValues . "');" . PHP_EOL;

                }
            }
        }

        return $js;
    }

}
