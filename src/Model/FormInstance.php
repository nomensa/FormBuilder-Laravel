<?php

namespace Nomensa\FormBuilder\Model;

use Illuminate\Database\Eloquent\Model;
use Nomensa\FormBuilder\Exceptions\InvalidSchemaException;
use Nomensa\FormBuilder\FormBuilder;

class FormInstance extends Model
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function formVersion()
    {
        return $this->belongsTo('App\FormVersion');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entryForm()
    {
        return $this->formVersion->entryForm();
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formSubmissions()
    {
        return $this->hasMany('App\FormSubmission', 'form_instance_id');
    }


    /**
     * @return \Nomensa\FormBuilder\FormBuilder
     */
    public function getFormBuilder() : FormBuilder
    {
        $formBuilder = $this->formVersion->getFormBuilder();
        $formBuilder->formInstance = $this;
        return $formBuilder;
    }

}
