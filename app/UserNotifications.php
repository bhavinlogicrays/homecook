<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserNotifications extends Model
{
    protected $table = 'user_notifications';

    public function client()
    {
        return $this->belongsTo('App\User', 'client_id', 'id');
    }

    // public function orderItem()
    // {
    //     return $this->belongsTo('App\Order', 'order_has_items', 'order_id', 'order_id')
    // }
}
