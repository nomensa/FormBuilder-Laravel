<?php

namespace Nomensa\FormBuilder\Model;

use Illuminate\Database\Eloquent\Model;

class FormParticipantStatus extends Model
{

    /**
     * @var string
     */
    protected $table = 'form_participant_statuses';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formParticipants()
    {
        return $this->hasMany('App\FormParticipant');
    }
}