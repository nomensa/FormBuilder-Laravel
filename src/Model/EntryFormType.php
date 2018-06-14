<?php

namespace Nomensa\FormBuilder\Model;

use Illuminate\Database\Eloquent\Model;

class EntryFormType extends Model
{

    /**
     * @var string
     */
    protected $table = 'entry_form_types';

    /**
     * @var bool
     */
    public $timestamps = true;

    public $dates = [
        'created_at',
        'updated_at'
    ];

    public function entryForms()
    {
        return $this->hasMany('App\EntryForm');
    }

}
