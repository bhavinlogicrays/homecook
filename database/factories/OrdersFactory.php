<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Order;
use Faker\Generator as Faker;

$factory->state(Order::class, 'recent', function ($faker) {
    return [
        'created_at'=>$faker->dateTimeBetween('-1 day', 'now')
    ];
});

$factory->define(Order::class, function (Faker $faker) {
    if(config('app.isqrsaas')){
        $resto_id=$faker->numberBetween(1,3);
        return [
            'updated_at' => now(),
            'table_id'=>$faker->numberBetween((($resto_id-1)*15)+1,$resto_id*15),
            'restorant_id'=>$resto_id,
            'payment_status'=>'paid',
            'comment'=>$faker->text,
            'order_price'=>$faker->numberBetween(30,100),
            'created_at'=>$faker->dateTimeBetween('-1 year', 'now')
        ];
    }else{
        return [
            'updated_at' => now(),
            'address_id'=>$faker->numberBetween(1,5),
            'client_id'=>$faker->numberBetween(4,5),
            'restorant_id'=>$faker->numberBetween(1,3),
            'payment_status'=>'paid',
            //'driver_id'=>3,
            //'phone'=>$faker->phoneNumber,
            'delivery_pickup_interval'=>'630_660',
            'comment'=>$faker->text,
            'delivery_price'=>$faker->numberBetween(5,10),
            'order_price'=>$faker->numberBetween(30,100),
            'created_at'=>$faker->dateTimeBetween('-1 year', 'now')
        ];
    }
    
});
