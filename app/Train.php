<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Train extends Model
{
    protected $fillable = [ 'name' ];

    public function stations()
    {
        return $this->belongsToMany('App\Station', 'train_stations');
    }

    public function ratings()
    {
        return $this->hasMany('App\Rating');
    }
}
