<?php

namespace Nomensa\FormBuilder\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\FormVersion;

class EntryForm extends Model
{


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entryFormType()
    {
        return $this->belongsTo('App\EntryFormType');
    }


    public function formVersions()
    {
        return $this->hasMany('App\FormVersion');
    }


    public function getCurrentFormVersionAttribute() : FormVersion
    {
        return $this->formVersions()->isCurrent()->first();
    }


    public function formInstances()
    {
        return $this->hasManyThrough('App\FormInstance','App\FormVersion');
    }


    public function getFormInstanceIdsAttribute() : array
    {
        return DB::table('form_instances')
            ->leftJoin('form_versions','form_version_id','=','form_versions.id')
            ->select('form_instances.id as form_instances_id')
            ->where('entry_form_id',$this->id)
            ->get()
            ->pluck('form_instances_id')
            ->toArray();
    }


    public function parentEntryForm()
    {
        return $this->belongsTo('App\EntryForm','id','form_child_id');
    }


    public function childEntryForm()
    {
        return $this->hasOne('App\EntryForm','id','form_child_id');
    }

}
