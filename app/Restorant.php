<?php

namespace App;

use willvincent\Rateable\Rateable;
use App\MyModel;

class Restorant extends MyModel
{
    use Rateable;

    protected $fillable = ['name','subdomain', 'user_id', 'lat','lng','address','phone','logo','description','city_id'];
    protected $appends = ['alias','logom','icon','coverm'];
    protected $imagePath='/uploads/restorants/';

    protected $casts = [
        'radius' => 'array',
    ];

    protected $attributes = [
        'radius' => '{}'
    ];



    /**
     * Get the user that owns the restorant.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getAliasAttribute()
    {
        return $this->subdomain;
    }


    public function getLogomAttribute()
    {
        return $this->getImge($this->logo,config('global.restorant_details_image'));
    }
    public function getIconAttribute()
    {
        return $this->getImge($this->logo,str_replace("_large.jpg","_thumbnail.jpg",config('global.restorant_details_image')),"_thumbnail.jpg");
    }

    public function getCovermAttribute()
    {
        return $this->getImge($this->cover,config('global.restorant_details_cover_image'),"_cover.jpg");
    }

    public function categories()
    {
        return $this->hasMany('App\Categories','restorant_id','id')->where(['categories.active' => 1]);
    }

    public function hours()
    {
        return $this->hasOne('App\Hours','restorant_id','id');
    }

    public function tables()
    {
        return $this->hasMany('App\Tables','restaurant_id','id');
    }

    public function areas()
    {
        return $this->hasMany('App\RestoArea','restaurant_id','id');
    }

    public function visits()
    {
        return $this->hasMany('App\Visit','restaurant_id','id');
    }

    public function orders()
    {
        return $this->hasMany('App\Order','restorant_id','id');
    }

    public function coupons()
    {
        return $this->hasMany('App\Coupons','restaurant_id','id');
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function(Restorant $restaurant) {

           if (env('is_demo',false)|env('IS_DEMO',false)){
            return false; //In demo disable deleting
           }else{
               //Delete orders
                foreach( $restaurant->orders()->get() as $order)
                {
                    //Delete Order items
                    //Delete Oders statuses
                    $order->delete();
                }


                //Delete Categories
                foreach( $restaurant->categories()->get() as $category)
                {
                    $category->delete();
                    //Delete items
                        //Delete extras
                        //Delete Options
                        //Deletee Options
                }



                //Delete Hours
                $restaurant->hours()->forceDelete();



                //Delete Tables
                $restaurant->tables()->forceDelete();

                //Delete Restoareas
                $restaurant->areas()->forceDelete();

                //Delete Visits to this restaruant
                $restaurant->visits()->forceDelete();

                return true;
           }


        });
    }


}
