<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RestoArea extends Model
{
    public $table="restoareas";
    protected $fillable = [
        'name','restaurant_id'
    ];

    public function tables()
    {
        return $this->hasMany('App\Tables','restoarea_id','id');
    }
}
