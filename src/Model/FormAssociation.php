<?php

namespace Nomensa\FormBuilder\Model;

use Illuminate\Database\Eloquent\Model;

class FormAssociation extends Model
{
    // Instruct Eloquent that we don't have an updated_at field on this table
    const UPDATED_AT = null;

    protected $fillable = [
        'type',
        'root_form_submission_id',
        'destination_form_submission_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rootFormSubmission()
    {
        return $this->belongsTo('App\FormSubmission', 'root_form_submission_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function destinationFormSubmission()
    {
        return $this->belongsTo('App\FormSubmission', 'destination_form_submission_id');
    }


    /**
     * $this->type is something like 'reflection_on', 'belongs_to' or 'feedback_on'
     * This method describes how the Root Form Submission relates to Destination Form Submission
     *
     * @return string
     */
    public function describeYourself() : string
    {
        return $this->rootFormSubmission->title . ' is ' . $this->type . ' on ' . $this->destinationFormSubmission->title;
    }

}
