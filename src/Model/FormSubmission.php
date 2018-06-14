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

}
