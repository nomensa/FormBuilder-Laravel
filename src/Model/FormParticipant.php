<?php

namespace Nomensa\FormBuilder\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FormParticipant extends Model
{

    /**
     * @var string
     */
    protected $table = 'form_participants';


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'updated_at',
        'created_at',
        'date_rejected',
        'date_signoff',
        'last_email_sent_at'
    ];


    /** The minimum number of days before a reminder can be sent */
    protected $daysBeforeReminderResend = 14;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function participant()
    {
        return $this->belongsTo('App\User', 'participant_user_id', 'id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function formInstance()
    {
        return $this->belongsTo('App\FormInstance');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function formSubmission()
    {
        return $this->belongsTo('App\FormSubmission');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function formParticipantStatus()
    {
        return $this->belongsTo('App\FormParticipantStatus');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function formParticipantType()
    {
        return $this->belongsTo('App\FormParticipantType');
    }


    /**
     * @return string
     */
    public static function createUUID()
    {
        return uniqid('', true);
    }


    /**
     * Wrapper for create that adds a UUID value
     *
     * @param array $inputs
     *
     * @return mixed
     */
    public static function createWithUUID(array $inputs)
    {
        // Add the UUID
        $inputs['uuid'] = self::createUUID();

        return self::create($inputs);
    }


    public function scopeGuests($query)
    {
        return $query->where('is_guest',1);
    }


    /**
     * If the form participant has not received an email in $this->daysBeforeReminderResend days
     * and their form participation is not complete, they can be sent another email.
     *
     * @return bool
     */
    public function canBeSentEmailReminder() : bool
    {
        // If the status is not "Invite sent", return false
        $inviteSentStatus = FormParticipantStatus::where('title', 'Invite sent')->firstOrFail();
        if ($this->form_participant_status_id != $inviteSentStatus->id) {
            return false;
        }

        $lastEmailDate = Carbon::parse($this->last_email_sent_at);
        if ($lastEmailDate->diffInDays(Carbon::now()) >= $this->daysBeforeReminderResend) {
            return true;
        }

        return false;
    }


}
