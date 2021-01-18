<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Image;
use Illuminate\Support\Str;
use App\Restorant;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param {String} folder
     * @param {Object} laravel_image_resource, the resource
     * @param {Array} versinos
     */
    public function saveImageVersions($folder,$laravel_image_resource,$versions){
        //Make UUID
        $uuid = Str::uuid()->toString();

        //Make the versions
        foreach ($versions as $key => $version) {
            if(isset($version['w'])&&isset($version['h'])){
                $img = Image::make($laravel_image_resource->getRealPath())->fit($version['w'], $version['h']);
                $img->save(public_path($folder).$uuid."_".$version['name']."."."jpg");
            }else{
                //Original image
                $laravel_image_resource->move(public_path($folder), $uuid."_".$version['name']."."."jpg");
            }
           
           
        }
        return $uuid;
    }

    private function withinArea($point, $polygon,$n)
    {
        if($polygon[0] != $polygon[$n-1])
            $polygon[$n] = $polygon[0];
        $j = 0;
        $oddNodes = false;
        $x = $point->lng;
        $y = $point->lat;
        for ($i = 0; $i < $n; $i++)
        {
            $j++;
            if ($j == $n)
            {
                $j = 0;
            }
            if ((($polygon[$i]->lat < $y) && ($polygon[$j]->lat >= $y)) || (($polygon[$j]->lat < $y) && ($polygon[$i]->lat >=$y)))
            {
                if ($polygon[$i]->lng + ($y - $polygon[$i]->lat) / ($polygon[$j]->lat - $polygon[$i]->lat) * ($polygon[$j]->lng - $polygon[$i]->lng) < $x)
                {
                    $oddNodes = !$oddNodes;
                }
            }
        }
        return $oddNodes;
    }

    function calculateDistance($latitude1, $longitude1, $latitude2, $longitude2, $unit) {
        $theta = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        switch($unit) {
          case 'Mi':
            break;
          case 'K' :
            $distance = $distance * 1.609344;
        }
        return (round($distance,2));
      }


    public function getAccessibleAddresses($restaurant,$addressesRaw){
        $addresses = [];
        $polygon = json_decode(json_encode($restaurant->radius));
        $numItems = $restaurant->radius?sizeof($restaurant->radius):0;


        if($addressesRaw){
            foreach($addressesRaw as $address){

                $point = json_decode('{"lat": '.$address->lat.', "lng":'.$address->lng.'}');

                if(!array_key_exists($address->id, $addresses)){
                    $new_obj = (object) [];
                    $new_obj->id = $address->id;
                    $new_obj->address = $address->address;

                    if(!empty($polygon)){
                        if(isset($polygon[0]) && $this->withinArea($point,$polygon,$numItems)){
                            $new_obj->inRadius = true;
                        }else{
                            $new_obj->inRadius = false;
                        }
                    }else{
                        $new_obj->inRadius = true;
                    }

                    $distance = floatval(round($this->calculateDistance($address->lat, $address->lng, $restaurant->lat, $restaurant->lng, "K")));

                    $rangeFound=false;
                    if(env('ENABLE_COST_PER_DISTANCE', false)){
                        //Range based pricing
                        
                        //Find the range
                        $ranges=[];

                        //Put the ranges
                        $ranges[0]=explode('-',env('RANGE_ONE','0-0'));
                        $ranges[1]=explode('-',env('RANGE_TWO','0-0'));
                        $ranges[2]=explode('-',env('RANGE_THREE','0-0'));
                        $ranges[3]=explode('-',env('RANGE_FOUR','0-0'));
                        $ranges[4]=explode('-',env('RANGE_FIVE','0-0'));

                        //Put the prices
                        $ranges[0][2]=floatval(explode('-',env('RANGE_ONE_PRICE','1')));
                        $ranges[1][2]=floatval(explode('-',env('RANGE_TWO_PRICE','1')));
                        $ranges[2][2]=floatval(explode('-',env('RANGE_THREE_PRICE','1')));
                        $ranges[3][2]=floatval(explode('-',env('RANGE_FOUR_PRICE','1')));
                        $ranges[4][2]=floatval(explode('-',env('RANGE_FIVE_PRICE','1')));

                        //Find the range
                        foreach ($ranges as $key => $range) {
                            if(floatval($range[0])<=$distance&&floatval($range[1])>=$distance){
                                $rangeFound=true;
                                $new_obj->cost_per_km=floatval($range[2]);
                                $new_obj->cost_total=floatval($range[2]);
                            }
                        }

                    }

                    if(!$rangeFound){
                        if(env('ENABLE_COST_PER_DISTANCE', false) && env('COST_PER_KILOMETER', 1)){
                            
                            $new_obj->cost_per_km=floor($distance)*floatval(env('COST_PER_KILOMETER'));
                            $new_obj->cost_total=floor($distance)*floatval(env('COST_PER_KILOMETER'));
                        }else{
                            //Use the static price for delivery
                            $new_obj->cost_per_km=config('global.delivery');
                            $new_obj->cost_total=config('global.delivery');
                        }
                    }
                    

                    $addresses[$address->id] = (object)$new_obj;
                }
            }
        }

        //dd($addresses);
        return $addresses;
    }


    public function getRestaurant(){
        if(!auth()->user()->hasRole('owner')){
           return null;
        }

        //Get restaurant for currerntly logged in user
        return Restorant::where('user_id',auth()->user()->id)->first();
    }

    public function ownerOnly(){
        if(!auth()->user()->hasRole('owner')){
            abort(403, 'Unauthorized action.');
        }
    }

    public function makeAlias($name){
        $cyr = array(
            'ж',  'ч',  'щ',   'ш',  'ю',  'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь', 'я',
            'Ж',  'Ч',  'Щ',   'Ш',  'Ю',  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', 'Я');
        $lat = array(
            'zh', 'ch', 'sht', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'y', 'x', 'q',
            'Zh', 'Ch', 'Sht', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', 'Y', 'X', 'Q');
        $name= str_replace( $cyr,$lat, $name);

        return strtolower(preg_replace('/[^A-Za-z0-9]/', '', $name));
    }


    public function scopeIsWithinMaxDistance($query, $latitude, $longitude, $radius = 25,$table="restorants") {

        
        $haversine = "(6371 * acos(cos(radians($latitude))
                        * cos(radians(".$table.".lat))
                        * cos(radians(".$table.".lng)
                        - radians($longitude))
                        + sin(radians($latitude))
                        * sin(radians(".$table.".lat))))";
        return $query
           ->select(['name','id']) //pick the columns you want here.
           ->selectRaw("{$haversine} AS distance")
           ->whereRaw("{$haversine} < ?", [$radius])
           ->orderBy('distance');
   }
}
