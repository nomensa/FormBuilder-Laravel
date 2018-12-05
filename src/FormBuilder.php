<?php

namespace Nomensa\FormBuilder;

use App\User;
use App\EntryFormInstance;
use App\FormSubmission;
use Illuminate\Support\MessageBag;

class FormBuilder
{

    use MarkerUpper;
    use FieldMapping;

    /** @var array of Instances of FormBuilder\Component */
    public $components = [];

    /** @var - Group of rules for how fields are displayed */
    public $ruleGroups;

    /** @var - mapping state_id to ruleGroup */
    public $stateRuleGroups;

    /** @var \App\FormInstance $formInstance */
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

    /** @var FormSubmission */
    public $formSubmission;

    /** @var MessageBag */
    public $errors;

    /** whether we render this form */
    public $render;


    public function __construct(array $form_schema, array $options)
    {
        $this->components = $form_schema;

        foreach ($this->components as &$component) {
            $component = new Component($component);
        }

        // build up rule groups by cascading through options->rules;
        $this->ruleGroups = $this->cascadeRuleGroups($options['rules']);

        // get optional map of state_ids to ruleGroups
        $this->stateRuleGroups = isset($options['stateRuleGroups']) ? (array) $options['stateRuleGroups'] : null;
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
     * @return string|boolean
     */
    public function ruleExists($fieldName, $needle)
    {
        $ruleChain = $this->getRule($fieldName);

        $rules = explode('|', $ruleChain);

        foreach ($rules as $rule) {
            if (explode(':', $rule)[0] == $needle) {
                return $rule;
            }
        }

        return false;
    }


    private function getRuleGroupKey()
    {
        $ruleGroupKey = 'default';

        // check state id
        $stateId = 'state-'.$this->state_id;

        // if state_id exists in stateRuleGroups then use that one
        if(isset($this->stateRuleGroups[$stateId])){
            $ruleGroupKey = $this->stateRuleGroups[$stateId];
        }

        if (!$this->formInstance) {
            return $ruleGroupKey;
        }

        switch ($this->formInstance->entryForm->code) {

            case 'RCOA_005':

                $ruleGroupKey = ($this->state_id == 2) ? 'signoff' : 'default';

                break;

            default:

                $ruleGroupKey = ($this->state_id == 2 && $this->formInstance->workflow && $this->formInstance->workflow->name == 'assessor-approval') ? 'signoff' : $ruleGroupKey;

                $ruleGroupKey = ($this->state_id == 1 && $this->formInstance->workflow && $this->formInstance->workflow->name == 'learner-approval') ? 'signoff-learner-approval' : $ruleGroupKey;

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
        return empty($this->formSubmission) == false;
    }


    /**
     * @param $row_name Value on `form_submission_fields`.`row_name`
     *
     * @return int - Number of row_groups
     */
    public function getRowGroupValueCount($row_name) : int
    {
        // If there is no submission, we want 1 of everything
        if ($this->formSubmission === null) {
            return 1;
        }
        $formSubmissionFields = $this->formSubmission->formSubmissionFields;
        $groups_indices = $formSubmissionFields->where('row_name', $row_name)->pluck('group_index');
        return $groups_indices->unique()->count();
    }


    /**
     * Takes a state and returns a nested array representing all the fields that can exist in the HTTP request.
     * Used by the Create and Update methods that deal with a POST request to modify fields in database.
     *
     * TODO: This should be extended to support cloneable row groups in future
     *
     * @param string $state - The state of the form (dictates which fields are hidden/editable/excluded/readonly)
     *
     * @return array
     */
    public function getRequestInputStructureInState($state): array
    {
        $structure = [];

        foreach ($this->components as $component) {
            if ($component->rowGroup) {
                foreach ($component->rowGroup->rows as $row) {
                    foreach ($row->columns as $column) {
                        if (in_array($column->states[$state], ['editable','hidden'])) {
                            $structure[$row->name][$column->field] = true;
                        }
                    }
                }
            }
        }

        return $structure;
    }


    /**
     * @param string $row_name
     * @param null|int $group_index
     * @param string $field_name
     *
     * @return string|\Carbon\Carbon $row->value or $row->value_date
     */
    public function getFieldValue($row_name, $group_index, $field_name)
    {
        if (!$this->hasSubmission()) {
            return null;
        }

        return $this->formSubmission->getFieldValue($row_name, $group_index, $field_name);
    }


    /**
     * Gets options for a single field from the form
     *
     * @param string $row_name
     * @param string $field_name
     *
     * @return array
     */
    public function getFieldOptions($row_name, $field_name) : array
    {
        $field = $this->getField($row_name, $field_name);
        if ($field) {
            return $field->options;
        }
        return [];
    }


    /**
     * Gets a single field from the form
     *
     * @param string $row_name
     * @param string $field_name
     *
     * @return null|Column
     */
    public function getField($row_name, $field_name)
    {
        foreach ($this->components as $component) {
            $field = $component->findField($row_name, $field_name);
            if ($field) {
                return $field;
            }
        }
        return null;
    }


    /**
     * @param string $row_name
     * @param string $field_name
     * @param string $value_key
     *
     * @return string
     */
    public function getFieldHumanValue($row_name, $field_name, $value_key) : string
    {
        $options = $this->getFieldOptions($row_name, $field_name);
        if (isSet($options[$value_key])) {
            return $options[$value_key];
        }
        return $value_key;
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
        return MarkerUpper::inlineFieldError($this->errors, $fieldName, $this->fieldMap);
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


    /**
     * @return bool
     */
    public function isReadOnly()
    {
        return ($this->displayMode === 'readonly');
    }


}
