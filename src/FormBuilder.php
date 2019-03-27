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

    /** DEPRECATED! An integer to describe state. Use $state_key below */
    public $state_id;

    /** @var string - Key in the 'states' arrays in the schema that describes editability & visibility of a field */
    public $state_key;

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
            $html .= $component->markup($this);
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

        $state_key = $this->getStateKey();

        // if state_id exists in stateRuleGroups then use that one
        if(isset($this->stateRuleGroups[$state_key])){
            $ruleGroupKey = $this->stateRuleGroups[$state_key];
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
        $rows = [];

        foreach ($this->components as $component) {
            if ($component->rowGroup) {
                foreach ($component->rowGroup->rows as $row) {

                    if ($row->cloneable) {

                        if (!isSet($rows[$row->name])) {
                            $rows[$row->name] = (object)[
                                'cloneable' => true,
                                'fields' => []
                            ];
                        }

                        foreach ($row->rows as $cloneableRow) {
                            foreach ($cloneableRow->columns as $column) {
                                $rows[$row->name]->fields[] = $column->field;
                            }
                        }

                    } elseif (is_array($row->columns)) {

                        if (!isSet($rows[$row->name])) {
                            $rows[$row->name] = (object)[
                                'cloneable' => false,
                                'fields' => []
                            ];
                        }

                        foreach ($row->columns as $column) {
                            if (isSet($column->states[$state])) {
                                if (in_array($column->states[$state], ['editable', 'hidden'])) {
                                    $rows[$row->name]->fields[] = $column->field;
                                }

                                // TODO: Temporary hack to account for:
                                //  - hidden-for-learner
                                //  - readonly_for_owner (Used by CUT, IAC, IACOA, ILTC)
                                //  - editable_if_true_else_readonly
                                // We can remove this when all implementations move away from db stored states
                                // and into controller method imposed states.
                                if (preg_match('/^(hidden|editable|readonly)(_|-)/', $column->states[$state])) {
                                    $rows[$row->name]->fields[] = $column->field;
                                }

                            } else {
                                // States were not defined in schema so let's assume it should be included in inputs
                                $rows[$row->name]->fields[] = $column->field;
                            }
                        }
                    }
                }
            }

            // If it's some funky custom component then the schema should contain field mappings
            if ($component->fieldMappings) {
                foreach ($component->fieldMappings as $field_key => $label) {
                    list($group_key, $field_key) = explode('.', $field_key);

                    if (!isSet($rows[$group_key])) {
                        $rows[$group_key] = (object)[
                            'cloneable' => false,
                            'fields' => []
                        ];
                    }

                    $rows[$group_key]->fields[] = $field_key;
                }
            }

        }

        return $rows;
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

        return self::findHumanValueIfAvailable($options, $value_key);
    }


    /**
     * Gets the human form of the option if it can be found, else falls back to returning the key
     *
     * @param array $options
     * @param string $key
     *
     * @return string
     */
    public static function findHumanValueIfAvailable(array $options, $key) : string
    {
        if (isSet($options[$key])) {
            return $options[$key];
        }

        // Iterate over the options, seeing if they are actually optgroups with options inside
        foreach ($options as $option) {
            if (is_array($option)) {
                if (isSet($option[$key])) {
                    return $option[$key];
                }
            }
        }

        // Give up, just return key
        return $key;
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
     * Returns either the default blank string or a maxChars set in user's config
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


    /**
     * @param string $state_key - The key in the array of states that are used on each field.
     *                            Traditionally called 'state-1' and 'state-2' but could equally
     *                            be called 'editing-name' or 'approving-feedback'.
     */
    public function setState(string $state_key)
    {
        $this->state_key = $state_key;
    }


    /**
     *
     *
     * @return string
     */
    public function getStateKey()
    {
        // Legacy support for deprecated state_id variable
        if ($this->state_id !== null && $this->state_key == null) {
            $this->state_key = 'state-' . $this->state_id;
        }

        return $this->state_key;
    }


}
