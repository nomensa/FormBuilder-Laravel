<?php

namespace Nomensa\FormBuilder\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class FormVersion extends Model
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entryForm()
    {
        return $this->belongsTo('App\EntryForm');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formInstances()
    {
        return $this->hasMany('App\FormInstance');
    }


    /**
     * @return int
     */
    public function formSubmissionCount() : int
    {
        return DB::table('form_submissions')
            ->leftJoin('form_instances','form_instance_id','=','form_instances.id')
            ->where('form_version_id',$this->id)
            ->count();
    }


    public function scopeIsCurrent($query)
    {
        return $query->where('is_current',1);
    }


    /**
     * @param $query
     * @param array $entryFormIds
     *
     * @return mixed
     */
    public function scopeByEntryFormIds($query,array $entryFormIds)
    {
        return $query->whereIn('entry_form_id',$entryFormIds);
    }

}
