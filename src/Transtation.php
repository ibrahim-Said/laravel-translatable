<?php

namespace Said\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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