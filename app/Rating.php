<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [ 'rating', 'departure', 'type' ];
    protected $table = 'ratings';

    public function train()
    {
        return $this->belongsTo('App\Train');
    }
}
