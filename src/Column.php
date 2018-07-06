<?php

namespace Nomensa\FormBuilder;

use Field;
use Form;
use Html;
use Auth;

use Carbon\Carbon;

use CSSClassFactory;

use Nomensa\FormBuilder\Exceptions\InvalidSchemaException;

class Column
{

    const MULTI_OPTION_TYPES = ['checkboxes', 'radios'];

    const WITH_LABEL = true;

    /** @var string */
    public $field = '';

    public $label = '';

    public $type = 'text';

    public $stateSpecificType = '';

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

    /** @var array Values for option-table */
    public $columnHeadings = [];
    
    /** @var array of HTML data attributes keyed by name (without "data-" prefix) */
    public $dataAttributes = [];


    /**
     * Column constructor.
     *
     * @param array $column_schema
     * @param bool $cloneable
     *
     * @throws \Nomensa\FormBuilder\Exceptions\InvalidSchemaException
     */
    public function __construct(array $column_schema, bool $cloneable)
    {
        if (isSet($column_schema['field'])) {
            $this->field = $column_schema['field'];
        } else {
            throw new InvalidSchemaException('Columns must have a "field" value');
        }

        if (isSet($column_schema['label'])) {
            $this->label = $column_schema['label'];
        } else {
            throw new InvalidSchemaException('Columns must have a "label" value');
        }

        if (isSet($column_schema['type'])) {
            $this->type = $column_schema['type'];
        } else {
            throw new InvalidSchemaException('Columns must have a "type" value');
        }

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

        } else {
            if (is_array($column_schema['options'])) {
                if (!$this->hasStringKeys($column_schema['options'])) {
                    $column_schema['options'] = $this->slugKeyArray($column_schema['options'], ($this->type == 'radios'));
                }
                $this->options = $column_schema['options'];
            }
        }

        $this->columnHeadings = $column_schema['column-headings'] ?? null;
    }


    /**
     * Temporary poly-fill until schemas only contain key-pair values
     *
     * @param array $array
     *
     * @return bool
     */
    function hasStringKeys(array $array): bool
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
     *
     * @var $withLabel
     *
     * @return array
     */
    private function asFormArray($withLabel = false)
    {
        $simpleColumn = [
            'id' => $this->id,
            'disabled' => $this->disabled,
            'label' => false,
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
     * @param \Nomensa\FormBuilder\FormBuilder $formBuilder
     * @param $group_index
     *
     * @return string
     */
    private function markupField(FormBuilder $formBuilder, $group_index)
    {
        $output = '';

        if ($this->value === null) {
            $this->value = $this->parseDefaultValue($formBuilder);
        }

        if ($this->cloneable) {
            // Replace the zero surrounded by dots with the clone number
            $this->fieldNameWithBrackets = preg_replace('/\[0\]/', '[' . $group_index . ']', $this->fieldNameWithBrackets);
        }

        switch ($this->stateSpecificType) {

            case "hidden":
            case "ignore":
                // do not render this field at all
                return '';
                break;

            case "checkbox":

                return Field::checkbox($this->fieldNameWithBrackets, $this->options, $this->value, $this->asFormArray(Column::WITH_LABEL));
                break;

            case "checkboxes":

                $values = json_decode($this->value, true) ?? [];

                $attributes = $this->asFormArray(Column::WITH_LABEL);
                $origID = $attributes['id'];
                foreach ($this->options as $key => $option) {
                    $attributes['label'] = $option;
                    $attributes['id'] = $origID . '_' . $key;
                    $output .= Field::checkbox($this->fieldNameWithBrackets . '[]', $key, in_array($key, $values), $attributes);
                }
                return $output;
                break;

            case "checkboxes-readonly": /* Render text into the form and add hidden fields */

                $attributes = $this->asFormArray(Column::WITH_LABEL);

                $values = json_decode($this->value, true);
                if ($values === null) {
                    return '';
                }
                $output .= '<div class="' . $this->classBundle . '">';
                $output .= '<div class="section-readonly">';
                $output .= MarkerUpper::wrapInTag($this->label, "h4");
                $origID = $attributes['id'];

                if (is_array($values)) {
                    $output .= '<ul id="' . $origID . '_values">';
                    foreach ($values as $i => $value) {
                        $output .= Field::hidden($this->fieldNameWithBrackets . '[]', $value);
                        $output .= '<li>' . $this->options[$value] . '</li>';
                    }
                    $output .= '</ul>';
                }
                $output .= '</div>' . PHP_EOL . '<!-- /.section-readonly -->' . PHP_EOL;
                $output .= '</div>' . PHP_EOL;
                return $output;
                break;

            case "select":

                $attributes = $this->asFormArray();
                $attributes['class'] = CSSClassFactory::selectClassBundle();

                return Form::select($this->fieldNameWithBrackets, $this->options, $this->value, $attributes);
                break;

            case "radios":

                return Form::{$this->type}($this->fieldNameWithBrackets, $this->options, $this->value, $this->asFormArray());
                break;

            case "option-table":
                // define table headers from first row
                $headers = $this->columnHeadings;

                $output = '<table class="table table-active table-hide-fooicon" data-expand-all="true" data-toggle-column="last">';
                $output .= '<thead>';

                $output .= '<tr>';
                $output .= '<th></th>';

                foreach ($headers as $header => $value) {
                    $output .= "<th>$value</th>";
                }

                $output .= '</tr>';
                $output .= '</thead>';
                $output .= '<tbody>';

                foreach ($this->options as $value => $cells) {

                    $selected = $this->value == $value ? true : false;

                    $output .= '<tr>';

                    $output .= "<td>" . Form::radio($this->fieldNameWithBrackets, $value, $selected, ['id' => $this->fieldNameWithBrackets.'_'.$value]) . "</td>";

                    foreach ($cells as $cell) {
                        $output .= "<td>".Form::label($this->fieldNameWithBrackets.'_'.$value,$cell)."</td>";
                    };

                    $output .= '</tr>';
                }

                $output .= '</tbody>';
                $output .= '</table>';

                break;

            case "file":

                return Field::file($this->fieldNameWithBrackets, $this->asFormArray());
                break;

            case "date":

                if ($formBuilder->ruleExists($this->fieldName, 'date_is_in_the_past')) {
                    $this->dataAttributes['mindate'] = '-5y';
                    $this->dataAttributes['maxdate'] = 0;
                }

                if ($formBuilder->ruleExists($this->fieldName, 'date_is_in_the_future')) {
                    $this->dataAttributes['mindate'] = 0;
                    $this->dataAttributes['maxdate'] = '+5y';
                }

                if ($formBuilder->ruleExists($this->fieldName, 'date_is_within_the_last_month')) {
                    $this->dataAttributes['mindate'] = '-1m';
                    $this->dataAttributes['maxdate'] = 0;
                }

                $rule = $formBuilder->ruleExists($this->fieldName, 'before');
                if ($rule && $rule == "before:today") {
                    $this->dataAttributes['mindate'] = '-5y';
                    $this->dataAttributes['maxdate'] = 0;
                }

                if ($this->value) {
                    $this->value = $this->value->format('Y-m-d');
                }

                // We create date as a text field (NOT date!) because we replace it with a data picker and don't want Chrome to be "helpful"
                return Field::text($this->fieldNameWithBrackets, $this->value, $this->asFormArray());
                break;

            case "password":

                return Form::bsPassword($this->fieldNameWithBrackets, $this->value, $this->asFormArray());
                break;

            case "date-readonly":  /* Render text into the form and add a hidden field */

                if (!empty($this->value)) {

                    $output .= '<div class="' . $this->classBundle . '">';
                    $output .= '<div class="section-readonly">';
                    $output .= MarkerUpper::wrapInTag($this->label, "h4");
                    $output .= MarkerUpper::wrapInTag($this->value->format('j F Y'), 'p');
                    $output .= '</div>' . PHP_EOL . '<!-- /.section-readonly -->' . PHP_EOL;
                    $output .= '</div>' . PHP_EOL;
                    $output .= Field::hidden($this->fieldNameWithBrackets,
                        $this->value->format('Y-m-d'), $this->asFormArray());
                }

                break;

            case "radios-readonly":  /* Render text into the form and add a hidden field */
            case "select-readonly":  /* Render text into the form and add a hidden field */

                if (!empty($this->value)) {
                    $output .= '<div class="' . $this->classBundle . '">';
                    $output .= '<div class="section-readonly">';

                    if (isset($this->parentTitle)) {
                        $output .= MarkerUpper::wrapInTag($this->parentTitle, "h3");
                    }

                    $output .= MarkerUpper::wrapInTag($this->label, "h4");
                    $output .= MarkerUpper::wrapInTag($this->options[$this->value], 'p');
                    $output .= '</div>' . PHP_EOL . '<!-- /.section-readonly -->' . PHP_EOL;
                    $output .= '</div>' . PHP_EOL;
                    $output .= Field::hidden($this->fieldNameWithBrackets, $this->value, $this->asFormArray());
                }
                break;

            case "text-readonly":  /* Render text into the form and add a hidden field */
            case "number-readonly":
            case "textarea-readonly":  /* Render text into the form and add a hidden field */

                if (!empty($this->value)) {
                    $output .= '<div class="' . $this->classBundle . '">';
                    $output .= '<div class="section-readonly">';
                    $output .= MarkerUpper::wrapInTag($this->label, "h4");
                    $output .= MarkerUpper::wrapInTag($this->value, 'div');
                    $output .= '</div>' . PHP_EOL . '<!-- /.section-readonly -->' . PHP_EOL;
                    $output .= Field::hidden($this->fieldNameWithBrackets, $this->value, $this->asFormArray());
                    $output .= '</div>' . PHP_EOL;

                }
                break;

            case "option-table-readonly":
                // define table headers from first row
                $headers = $this->columnHeadings;

                $output .= '<div class="' . $this->classBundle . '">';
                $output .= '<div class="section-readonly">';

                $output .= '<table class="table table-active table-hide-fooicon" data-expand-all="true" data-toggle-column="last">';
                $output .= '<thead>';

                $output .= '<tr>';

                foreach ($headers as $header => $value) {
                    $output .= "<th>$value</th>";
                }

                $output .= '</tr>';
                $output .= '</thead>';
                $output .= '<tbody>';

                foreach ($this->options as $value => $cells) {

                    // only display the selected row
                    if ($this->value == $value) {
                        $output .= '<tr>';

                        foreach ($cells as $cell) {
                            $output .= "<td>" . $cell . "</td>";
                        };

                        $output .= '</tr>';
                    }
                }

                $output .= '</tbody>';
                $output .= '</table>';

                $output .= '</div>' . PHP_EOL . '<!-- /.section-readonly -->' . PHP_EOL;
                $output .= '</div>' . PHP_EOL;
                $output .= Field::hidden($this->fieldNameWithBrackets, $this->value, $this->asFormArray());

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
     * @param \Nomensa\FormBuilder\FormBuilder $formBuilder
     * @param int $totalCols
     * @param null|int $group_index
     *
     * @return \Nomensa\FormBuilder\MarkUp
     */
    public function markup(FormBuilder $formBuilder, $totalCols, $group_index): MarkUp
    {
        $this->classBundle = CSSClassFactory::colClassBundle($totalCols);
        $this->classBundle->add($this->classes);

        $this->rules = isset($this->rules) ? $this->rules : null;
        $this->field = isset($this->field) ? $this->field : $this->fieldName;
        $this->selected = isset($this->selected) ? $this->selected : null;
        $this->saved = isset($this->saved) ? $this->saved : null;
        $this->label = $this->label ?? null;
        $this->displayMode = $formBuilder->displayMode;

        $this->value = $formBuilder->getFieldValue($this->row_name, $group_index, $this->field);

        if ($this->value && !empty($this->helptextIfPreviouslySaved)) {
            $this->helptext = $this->helptextIfPreviouslySaved;
        }

        $output = '';

        $state = $this->getState($formBuilder);

        // if access for your state determines content is hidden then don't render the field
        if ($state == 'hidden-for-learner' && $formBuilder->formInstance->workflow->name != 'learner-approval') {
            return new MarkUp('', MarkUp::NO_VISIBLE_CONTENT);
        }

        /** check if variable exists in viewData and set state as editable if it does and is true */
        if (preg_match('/^editable_if_true_else_ignore:(.*)$/', $state, $matches)) {
            $keyName = $matches[1];

            $state = ($formBuilder->viewData[$keyName] == true) ? 'editable' : 'ignore';
        }

        /** check if variable exists in viewData and set state as editable if it does and is true */
        if (preg_match('/^editable_if_true_else_readonly:(.*)$/', $state, $matches)) {
            $keyName = $matches[1];

            $state = ($formBuilder->viewData[$keyName] == true) ? 'editable' : 'readonly';
        }

        $this->stateSpecificType = $this->type;

        if ($state == 'readonly_for_owner' && $formBuilder->owner->id == Auth::user()->id ||
            $state == 'editable_for_owner' && $formBuilder->owner->id != Auth::user()->id ||
            $state != 'hidden' && $this->displayMode == 'readonly' ||
            $state == 'readonly') {
            $state = 'readonly';
            $this->stateSpecificType = $this->type . '-' . $state;
        }

        if ($state == 'hidden') {
            $this->stateSpecificType = 'hidden';
        }

        if ($state == 'ignore') {
            $this->stateSpecificType = 'ignore';
        }


        $optional = $formBuilder->ruleExists($this->fieldName, 'nullable') ? '<span class="optional"> ' . __('validation.optional_field') . '</span>' : null;

        $inlineErrors = $formBuilder->getInlineFieldError($this->fieldName);

        if (!empty($inlineErrors)) {
            $this->classBundle->add("errors");
        }

        $columnHTML = $this->markupField($formBuilder, $group_index);

        // if type is neither ignore  or hidden or readonly, wrap it in a container div
        if ($this->stateSpecificType != 'ignore' && $this->stateSpecificType != 'hidden' && $state != 'readonly') {

            $output .= $formBuilder->getErrorAnchor($this->fieldName);

            $output .= '<div class="' . $this->classBundle . '">';
            if (isset($this->prefix)) {
                $output .= MarkerUpper::wrapInTag($this->prefix, 'div', ['id' => $this->id]);
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

            $output .= $columnHTML;

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

        } elseif ($state == 'readonly' && strlen($columnHTML)) {

            return new MarkUp($columnHTML);

        } else {

            return new MarkUp($columnHTML, MarkUp::NO_VISIBLE_CONTENT);

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
    private function parseDefaultValue(FormBuilder $formBuilder)
    {
        if ($this->default_value === 'TODAY') {
            return Carbon::now();
        }

        if ($this->default_value === 'INCREMENTS_FOR_USER') {
            $maxVal = $formBuilder->owner->formSubmissionFields
                ->where('row_name', $this->row_name)
                ->where('field_name', $this->field)
                ->max('value');

            return (int)$maxVal + 1;
        }

        return $this->default_value;
    }

}
