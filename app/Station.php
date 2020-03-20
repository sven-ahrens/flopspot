<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $fillable = [ 'name' ];

    public function trains()
    {
        return $this->belongsToMany('App\Train', 'train_stations');
    }
}
