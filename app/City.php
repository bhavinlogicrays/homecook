<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\MyModel;

class City extends MyModel
{
    use SoftDeletes;
    protected $table = 'cities';
    protected $imagePath='/uploads/settings/';

    protected $fillable = ['name','alias', 'image','header_title','header_subtitle'];
    protected $appends = ['logo'];

    public function getLogoAttribute()
    {
        return $this->getImge($this->image,config('global.restorant_details_image'));
    }

    public static function boot() 
    {
        parent::boot();
        self::deleting(function(City $city) {
           if (env('is_demo',false)|env('IS_DEMO',false)){
            return false; //In demo disable deleting
           }else{
            return true;
           }
        });
    }
    
}
