<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerCardDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'picture', 'notes', 'identifiers', 'custom_list_1', 'custom_list_2', 'custom_list_3', 'department', 'custom_field_1', 'custom_field_2', 'custom_field_3', 'lead_source', 'lead_status', 
    ];

    public function user(){
    	return $this->belongsTo('App\User','user_id','id');
    }
}
