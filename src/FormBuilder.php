<?php

namespace Nomensa\FormBuilder;

use App\User;
use App\EntryFormInstance;
use App\EntryFormSubmission;
use Illuminate\Support\MessageBag;

class FormBuilder
{

    use MarkerUpper;
    use FieldMapping;

    /** @var array of Instances of FormBuilder\Component */
    public $components = [];

    /** @var Group of rules for how fields are displayed */
    public $ruleGroups;

    /** @var App\EntryFormInstance $formInstance */
    public $formInstance;

    /**  A key in the 'access' array in the schema that describes how a field is rendered */
    public $state_id;

    /** Whether to override field display rules from access state  */
    public $displayMode;

    /** @var array - Any additional variables that need to be made available */
    public $viewData;

    /** @var User */
    public $owner;

    /** @var Any class that implements CSSClassProvider */
    public $cssClassProvider;

    /** @var EntryFormSubmission */
    public $entryFormSubmission;

    /** @var MessageBag */
    public $errors;

    /** whether we render this form */
    public $render;


    public function __construct(array $form_schema, $options)
    {
        $this->components = $form_schema;

        foreach ($this->components as &$component) {
            $component = new Component($component);
        }

        // build up rule groups by cascading through options->rules;
        $this->ruleGroups = $this->cascadeRuleGroups((array)$options->rules);

    }


    /**
     * Cascade through ruleGroups and append previous rules
     *
     * @param $ruleGroups
     *
     * @return mixed
     */
    private function cascadeRuleGroups($ruleGroups)
    {

        $prev = [];

        // decode $ruleGroups object into nested array
        $ruleGroupArray = json_decode(json_encode($ruleGroups), true);

        foreach ($ruleGroupArray as $index => $ruleGroup) {

            // merge the previous values
            $ruleGroup = array_merge( $prev, $ruleGroup );

            // save the last result for our next loop
            $prev = $ruleGroup;

            // encode the values into an object
            $ruleGroups[$index] =  json_decode(json_encode($ruleGroup), FALSE);

        }

        return $ruleGroups;

    }


    /**
     * @return string HTML markup
     */
    public function markup()
    {
        $html = '';
        foreach ($this->components as $component) {
            $html .= $component->markup($this, $this->state_id);
        }
        return $html;
    }


    /**
     * @param $fieldName
     * @param string $needle Rule keyword to look for eg 'nullable'
     *
     * @return boolean
     */
    public function ruleExists($fieldName, $needle)
    {
        $ruleChain = $this->getRule($fieldName);

        $rules = explode('|', $ruleChain);

        foreach ($rules as $rule) {
            if (explode(':', $rule)[0] == $needle) {
                return true;
            }
        }

        return false;
    }


    private function getRuleGroupKey()
    {
        $ruleGroupKey = 'default';

        switch ($this->formInstance->entryForm->code) {

            case 'RCOA_005':

                $ruleGroupKey = ($this->state_id == 2) ? 'signoff' : 'default';

                break;

            default:

                $ruleGroupKey = ($this->state_id == 2 && $this->formInstance->workflow->name == 'assessor-approval') ? 'signoff' : $ruleGroupKey;

                $ruleGroupKey = ($this->state_id == 1 && $this->formInstance->workflow->name == 'learner-approval') ? 'signoff-learner-approval' : $ruleGroupKey;

                break;
        }

        return $ruleGroupKey;
    }

    /**
     * TODO Unit Test for this
     *
     * @param string $fieldName
     *
     * @return string A HTML Form style validation string
     */
    public function getRule($fieldName)
    {
        $ruleGroupKey = $this->getRuleGroupKey();

        $ruleGroup = $this->getRuleGroup($ruleGroupKey);

        if (isSet($ruleGroup[$fieldName])) {
            return $ruleGroup[$fieldName];
        }
    }


    /**
     * TODO Unit Test for this
     *
     * @param string $key
     *
     * @return array
     */
    public function getRuleGroup($key)
    {
        
        if (isSet($this->ruleGroups[$key])) {
            return (array)$this->ruleGroups[$key];
        }
        return [];
    }


    /**
     * Indicates if a submission of the form exists in the database
     *
     * @return boolean
     */
    public function hasSubmission()
    {
        return empty($this->entryFormSubmission) == false;
    }


    /**
     * @param string $row_name
     * @param string $field_name
     *
     * @return string $row->value or $row->date_value
     */
    public function getFieldValue($row_name, $field_name)
    {

        if (!$this->hasSubmission()) {
            return null;
        }
        $submissionRows = $this->entryFormSubmission->formSubmissionFields;

        $row = $submissionRows->where('row_name', $row_name)
          ->where('field_name', $field_name)
          ->first();

        if (empty($row)) {
            return null;
        }

        /* if we have a value set in date_value, return this */
        if ($row->date_value) {
            return $row->date_value;
        }

        return $row->value;
    }


    public function getErrorAnchor($fieldName)
    {
        if (!empty($this->errors->get($fieldName))) {
            return MarkerUpper::wrapInTag('', 'a', [
              'name' => MarkerUpper::makeErrorAnchorName($fieldName),
              'class' => 'error-anchor',
            ]);
        }
        return '';
    }

    public function getInlineFieldError($fieldName)
    {

        return MarkerUpper::inlineFieldError($this->errors, $fieldName,
          $this->fieldMap);
    }

    /**
     * Returns either the default blank string or a prefix set in user's config
     */
    public static function getRowPrefix()
    {
        if (!class_exists('config')) {
            return '';
        }
        return config('formBuilder.rowPrefix') ?? '';
    }


    /**
     * Returns either the default blank string or a prefix set in user's config
     */
    public static function getMaxChars()
    {
        if (!class_exists('config')) {
            return null;
        }
        return config('formBuilder.maxChars') ?? null;
    }


}
