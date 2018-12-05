<?php

namespace Nomensa\FormBuilder\Model;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function formInstance()
    {
        return $this->belongsTo('App\FormInstance');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formSubmissionFields()
    {
        return $this->hasMany('App\FormSubmissionField', 'form_submission_id');
    }


    /**
     * Updates fields based on schema in a specific state
     *
     * @param string $rowPrefix
     * @param array $inputs
     * @param string $state
     *
     * @return array
     */
    protected function updateFieldsInState($rowPrefix, $inputs, $state)
    {
        $fields = $inputs[$rowPrefix];

        $formBuilder = $this->formInstance->formVersion->getFormBuilder();

        $inputStructure = $formBuilder->getRequestInputStructureInState($state);

        $i = 0;

        // Iterate over only the fields that should exist in this state (ignore any others that may have been injected)
        foreach ($inputStructure as $row_name => $row) {
            foreach (array_keys($row) as $name) {
                if (isSet($fields[$row_name][$name])) {

                    $value = $fields[$row_name][$name];

                    // TODO: This should be extended to support cloneable row groups in future
                    $this->updateFieldIfExists($i, $row_name, null, $name, $value);
                    $i++;

                }
            }
        }

        return $inputs;
    }


}
