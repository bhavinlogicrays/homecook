<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tables extends Model
{
    protected $fillable = [
        'name','restaurant_id','restoarea_id','size'
    ];

    public function restoarea()
    {
        return $this->belongsTo('App\RestoArea');
    }

    public function visits()
    {
        return $this->hasMany('App\Visit','table_id','id');
    }

    public function restaurant()
    {
        return $this->belongsTo('App\Restorant');
    }
}
