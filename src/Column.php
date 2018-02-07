<?php

namespace Nomensa\FormBuilder;

use Field;
use Form;
use Html;
use Session;
use Auth;

use Carbon\Carbon;

use CSSClassFactory;

class Column
{
    const MULTI_OPTION_TYPES = ['checkboxes', 'radios'];

    const WITH_LABEL = true;

    /** @var string */
    public $field = '';
    public $label = '';
    public $type = "text";

    private $default_value;

    public $fieldName;
    public $id;
    public $value;

    /** @var bool */
    public $cloneable = false;

    public $row_name;
    public $parentTitle;
    public $disabled;
    public $helptext;
    public $helptextIfPreviouslySaved;
    public $prefix;
    public $errors;
    public $fieldNameWithBrackets;
    public $toolbar;

    /** @var array of HTML tag attributes */
    public $attributes = [];

    /** @var array of display states */
    public $states;

    public $displayMode;


    /** @var array */
    public $classes;

    /** @var \Nomensa\FormBuilder\ClassBundle */
    public $classBundle;

    /** @var array Values in a select box */
    public $options = [];

    /** @var array of HTML data attributes keyed by name (without "data-" prefix) */
    public $dataAttributes = [];

    /**
     * Column constructor.
     *
     * @param array $column_schema
     * @param bool $cloneable
     */
    public function __construct(array $column_schema, bool $cloneable)
    {
        $this->field = $column_schema['field'];
        $this->label = $column_schema['label'];
        $this->type = $column_schema['type'];

        $this->default_value = $column_schema['default_value'] ?? null;

        $this->toolbar = $column_schema['toolbar'] ?? null;
        $this->attributes = $column_schema['attributes'] ?? [];
        $this->states = $column_schema['states'] ?? [];
        $this->value = '';

        $this->cloneable = $cloneable;

        $this->prefix = $column_schema['prefix'] ?? null;

        $this->displayMode = $column_schema['displayMode'] ?? null;
        $this->parentTitle = $column_schema['parentTitle'] ?? null;
        $this->classes = $column_schema['classes'] ?? null;
        $this->disabled = $column_schema['disabled'] ?? null;
        $this->helptext = $column_schema['helptext'] ?? null;
        $this->helptextIfPreviouslySaved = $column_schema['helptextIfPreviouslySaved'] ?? null;
        $this->row_name = $column_schema['row_name'];
        $this->errors = $column_schema['errors'] ?? null;

        // Construct field name
        $fieldName = FormBuilder::getRowPrefix() . '.' . $this->row_name;
        if ($this->cloneable) {
            $fieldName .= '.0'; // TODO Make this increment
        }
        $fieldName .= '.' . $this->field;

        $this->fieldName = trim($fieldName, '.');
        $this->fieldNameWithBrackets = MarkerUpper::htmlNameAttribute($this->fieldName);

        // Underscore version
        $this->id = MarkerUpper::HTMLIDFriendly($this->fieldName);



        if (!isSet($column_schema['options'])) {
            // Do nothing

        } else if (is_array($column_schema['options'])) {
            if (!$this->hasStringKeys($column_schema['options'])) {
                $column_schema['options'] = $this->slugKeyArray($column_schema['options'], ($this->type == 'radios') );
            }
            $this->options = $column_schema['options'];
        }
    }

    /**
     * Temporary poly-fill until schemas only contain key-pair values
     *
     * @param array $array
     * @return bool
     */
    function hasStringKeys(array $array) : bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    function slugKeyArray(array $array, $stripFirst = false)
    {
        if ($stripFirst) {
            array_shift($array);
        }
        $associativeArray = [];
        foreach ($array as $item) {
            if (strtolower($item) == 'please select') {
                $associativeArray[""] = $item;
            } else {
                $associativeArray[str_slug($item)] = $item;
            }
        }
        return $associativeArray;
    }


    /**
     * Returns an array containing only the column parameters required by LaravelCollective Form/Field
     * @var $withLabel
     *
     * @return array
     */
    private function asFormArray($withLabel = false)
    {
        $simpleColumn = [
            'id' => $this->id,
            'disabled' => $this->disabled,
            'label' => false
        ];

        if ($withLabel === Column::WITH_LABEL) {
            $simpleColumn['label'] = $this->label;
        }

        foreach ($this->dataAttributes as $key => $value) {
            $dataAttributeKey = 'data-' . $key;
            $simpleColumn[$dataAttributeKey] = $value;
        }

        $simpleColumn = array_merge($simpleColumn, $this->attributes);

        return $simpleColumn;
    }


    /**
     * @param FormBuilder $formBuilder
     *
     * @return \Illuminate\Support\HtmlString|string
     */
    private function markupField(FormBuilder $formBuilder)
    {
        $output = '';

        switch ($this->type) {

            case "ignore":
                // do not render this field at all
                return '';

                break;

            case "checkbox":

                return Field::checkbox($this->fieldNameWithBrackets, $this->options, $this->value, $this->asFormArray(Column::WITH_LABEL));
                break;

            case "checkboxes":

                $attributes = $this->asFormArray(Column::WITH_LABEL);
                $origID = $attributes['id'];
                foreach ($this->options as $key => $option) {
                    $attributes['label'] = $option;
                    $attributes['id'] = $origID . '_' . $key;
                    $output .= Field::checkbox($this->fieldNameWithBrackets . '[]', $option, $this->value, $attributes);
                }
                return $output;
                break;

            case "select":

                $attributes = $this->asFormArray();
                $attributes['class'] = CSSClassFactory::selectClassBundle();

                if ($this->value === null) {
                    $this->value = $this->parseDefaultValue();
                }

                return Form::select($this->fieldNameWithBrackets, $this->options, $this->value, $attributes);
                break;

            case "radios":

                if ($this->value === null) {
                    $this->value = $this->parseDefaultValue();
                }

                return Form::{$this->type}($this->fieldNameWithBrackets, $this->options, $this->value, $this->asFormArray());
                break;

            case "file":

                return Field::file($this->fieldNameWithBrackets, $this->asFormArray());
                break;

            case "date":

                $this->dataAttributes['mindate'] = $formBuilder->ruleExists($this->fieldName, 'date_is_in_the_past') ? '-5y' : 0;
                $this->dataAttributes['maxdate'] = $formBuilder->ruleExists($this->fieldName, 'date_is_in_the_future') ? '+5y' : 0;

                if ($this->value === null) {
                    $this->value = $this->parseDefaultValue();
                }

                // We create date as a text field (NOT date!) because we replace it with a data picker and don't want Chrome to be "helpful"
                return Field::text($this->fieldNameWithBrackets, $this->value, $this->asFormArray());
                break;

            case "password":

                return Form::bsPassword($this->fieldNameWithBrackets, $this->value, $this->asFormArray());
                break;

            case "date-readonly":  /* Render text into the form and add a hidden field */

                $formattedDate = '';

                $d = explode('-', $this->value);

                if (count($d) == 3) {
                    $dateString = $d[2] . '-' . $d[1] . '-' . $d[0];
                    $formattedDate = Carbon::parse(trim($dateString))->format('j F Y');
                }

                $output .= '<div class="' . $this->classBundle . '">';
                $output .= '<section class="section-readonly">';
                $output .= MarkerUpper::wrapInTag($this->label, "h4");
                $output .= MarkerUpper::wrapInTag($formattedDate,'p');
                $output .= '</section>';
                $output .= '</div>';
                $output .= Field::hidden($this->fieldNameWithBrackets, $this->value, $this->asFormArray());

                break;

            case "radios-readonly":  /* Render text into the form and add a hidden field */
            case "select-readonly":  /* Render text into the form and add a hidden field */

                if (!empty($this->value)) {
                    $output .= '<div class="' . $this->classBundle . '">';
                    $output .= '<section class="section-readonly">';

                    if (isset($this->parentTitle)) {
                        $output .= MarkerUpper::wrapInTag($this->parentTitle, "h3");
                    }

                    $output .= MarkerUpper::wrapInTag($this->label, "h4");
                    $output .= MarkerUpper::wrapInTag($this->options[$this->value],'p');
                    $output .= '</section>';
                    $output .= '</div>';
                    $output .= Field::hidden($this->fieldNameWithBrackets, $this->value, $this->asFormArray());
                }
                break;

            case "text-readonly":  /* Render text into the form and add a hidden field */

            case "textarea-readonly":  /* Render text into the form and add a hidden field */

                if (!empty($this->value)) {
                    $output .= '<div class="' . $this->classBundle . '">';
                    $output .= '<section class="section-readonly">';
                    $output .= MarkerUpper::wrapInTag($this->label, "h4");
                    $output .= MarkerUpper::wrapInTag($this->value,'p');
                    $output .= '</section>';
                    $output .= Field::hidden($this->fieldNameWithBrackets, $this->value, $this->asFormArray());
                    $output .= '</div>';

                }
                break;

            case "search":

                return Form::text($this->fieldNameWithBrackets, $this->value, $this->asFormArray());
                break;

            default:

                return Field::{$this->type}($this->fieldNameWithBrackets, $this->value, $this->asFormArray());
                break;

        }

        return $output;
    }




    /**
     * @param FormBuilder/FormBuilder $form
     *
     * @return \Nomensa\FormBuilder\MarkUp
     */
    public function markup(FormBuilder $formBuilder, $totalCols)
    {
        $this->classBundle = CSSClassFactory::colClassBundle($totalCols);
        $this->classBundle->add($this->classes);

        $this->rules = isset($this->rules) ? $this->rules : null;
        $this->field = isset($this->field) ? $this->field : $this->fieldName;
        $this->selected = isset($this->selected) ? $this->selected : null;
        $this->saved = isset($this->saved) ? $this->saved : null;
        $this->label = $this->label ?? null;
        $this->displayMode = $formBuilder->displayMode;


        $this->value = $formBuilder->getFieldValue($this->row_name, $this->field);

        if($this->value && !empty($this->helptextIfPreviouslySaved)) {
            $this->helptext = $this->helptextIfPreviouslySaved;
        }

        $output = '';

        /**
         * map fields to labels
         * TODO refactor this to use values parsed from schema.json
         */
        Session::put('fields.' . str_replace(['.'], '_', $this->fieldName),
            '<strong>' . $this->label . '</strong>');

        $state = $this->getState($formBuilder);

        // if access for your state determines content is hidden then don't render the field
        if ($state == 'hidden-for-learner' && $formBuilder->formInstance->workflow->name != 'learner-approval') {
            return new MarkUp('', MarkUp::NO_VISIBLE_CONTENT);
        }

        /** check if variable exists in viewData and set state as editable if it does and is true */
        if (preg_match('/^editable_if_true_else_ignore:(.*)$/',$state,$matches)) {
            $keyName = $matches[1];

            $state = ($formBuilder->viewData[$keyName] == true) ? 'editable' : 'ignore';
        }

        /** check if variable exists in viewData and set state as editable if it does and is true */
        if (preg_match('/^editable_if_true_else_readonly:(.*)$/',$state,$matches)) {
            $keyName = $matches[1];

            $state = ($formBuilder->viewData[$keyName] == true) ? 'editable' : 'readonly';
        }

        if ($state == 'readonly_for_owner' && $formBuilder->owner->id == Auth::user()->id ||
            $state == 'editable_for_owner' && $formBuilder->owner->id != Auth::user()->id ||
            $state != 'hidden' && $this->displayMode == 'readonly' ||
            $state == 'readonly')
        {
            $state = 'readonly';
            $this->type = $this->type . '-' . $state;
        }

        if ($state == 'hidden') {
            $this->type = 'hidden';
        }

        if ($state == 'ignore') {
            $this->type = 'ignore';
        }


        $optional = $formBuilder->ruleExists($this->fieldName, 'nullable') ? '<span class="optional"> ' . __('validation.optional_field') . '</span>' : null;


        $inlineErrors = $formBuilder->getInlineFieldError($this->fieldName);

        if (!empty($inlineErrors)) {
            $this->classBundle->add("errors");
        }

        // if type is neither ignore  or hidden or readonly, wrap it in a container div
        if($this->type != 'ignore' && $this->type != 'hidden' && substr($this->type, -9) != '-readonly') {
            $output .= $formBuilder->getErrorAnchor($this->fieldName);


            $output .= '<div class="' . $this->classBundle . '">';
            if (isset($this->prefix)) {
                $output .= MarkerUpper::wrapInTag($this->prefix, 'div', ['id'=>$this->id]);
            }

            if (in_array($this->type, $this::MULTI_OPTION_TYPES)) {

                $output .= '<fieldset id="' . str_replace('.', '_',
                        $this->fieldName) . '">';
                $output .= '<legend>' . $this->label . $optional . '</legend>';

            } else {
                if ($this->type != 'checkbox') {
                    $output .= HTML::decode(Form::label(str_replace('.', '_', $this->fieldName), $this->label . $optional));
                }
            }

            if ($inlineErrors) {
                $output .= $inlineErrors;
            }

            if (!empty($this->helptext)) {
                $output .= '<div class="help_text">' . $this->helptext . '</div>';
            }

            $output .= $this->markupField($formBuilder);

            if (!empty($this->suffix)) {
                $output .= MarkerUpper::wrapInTag($this->suffix, 'div');
            }

            if (!empty($this->anchor)) {
                $a = explode("-", $this->anchor);
                $output .= "<span class=\"help\"><a href=\"#" . $a[0] . "\">" . $a[1] . "</a></span>";
            }

            if (in_array($this->type, $this::MULTI_OPTION_TYPES)) {
                $output .= '</fieldset>';
            }

            $output .= '</div>';
        } elseif(substr($this->type, -9) == '-readonly'){

            return new MarkUp($this->markupField($formBuilder));

        } else {
            return new MarkUp($this->markupField($formBuilder), MarkUp::NO_VISIBLE_CONTENT);
        }

        return new MarkUp($output);
    }


    /**
     * @param \Nomensa\FormBuilder\FormBuilder $formBuilder
     *
     * @return string Defaults to 'editable'
     */
    private function getState(FormBuilder $formBuilder)
    {
        $key = 'state-' . $formBuilder->state_id;
        if (isSet($this->states[$key])) {
            return $this->states[$key];
        }
        return 'editable';
    }

    /**
     * Returns the default value or a dynamically generated default if default is a keyword like "TODAY"
     *
     * @return null|string
     */
    private function parseDefaultValue()
    {
        if ($this->default_value === 'TODAY') {
            return Carbon::now()->format('d-m-Y');
        }
        return $this->default_value;
    }

}
