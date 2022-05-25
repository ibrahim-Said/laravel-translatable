<?php

namespace Said\Translatable\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable=[
        'locale',
        'column',
        'content',
        'translatable_type',
        'translatable_id'
    ];

    public function translatable(){
        return $this->morphTo();   
    }
}
