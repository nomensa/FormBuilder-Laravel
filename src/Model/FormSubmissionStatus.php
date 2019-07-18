<?php

namespace Nomensa\FormBuilder\Model;

use Illuminate\Database\Eloquent\Model;

class FormSubmissionStatus extends Model
{

    /**
     * @var string
     */
    protected $table = 'form_submission_statuses';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formSubmissions()
    {
        return $this->hasMany('App\FormSubmission');
    }
}
