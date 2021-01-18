<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Status;

class Status extends Model
{
    protected $table = 'status';
    public $timestamps = false;
    protected $fillable = [
        'name','alias'
    ];
}
