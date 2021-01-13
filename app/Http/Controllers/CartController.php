<?php

namespace App\Http\Controllers;

use Cart;
use App\Items;
use App\Tables;
use App\Restorant;
use App\Order;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use App\Models\Variants;

class CartController extends Controller
{
    public function add(Request $request){
        $item = Items::find($request->id);
        $restID=$item->category->restorant->id;

        //Check if added item is from the same restorant as previus items in cart
        $canAdd = false;
        if(Cart::getContent()->isEmpty()){
            $canAdd = true;
        }else{
            $canAdd = true;
            foreach (Cart::getContent() as $key => $cartItem) {
                if($cartItem->attributes->restorant_id."" != $restID.""){
                    $canAdd = false;
                    break;
                }
            }
        }

        //TODO - check if cart contains, if so, check if restorant is same as pervios one

       // Cart::clear();
        if($item && $canAdd){

            //are there any extras
            $cartItemPrice=$item->price;
            $cartItemName=$item->name;
            $theElement="";

            //Is there a varaint


            //variantID
            if($request->variantID){
                //Get the variant
                $variant=Variants::findOrFail($request->variantID);

                //Validate is this variant is from the current item
                if($variant->item->id==$item->id){
                    $cartItemPrice=$variant->price;

                    //For each option, find the option on the
                    $cartItemName=$item->name." ".$variant->optionsList;
                    //$theElement.=$value." -- ".$item->extras()->findOrFail($value)->name."  --> ". $cartItemPrice." ->- ";
                }
            }


            foreach ($request->extras as $key => $value) {

                $cartItemName.="\n+ ".$item->extras()->findOrFail($value)->name;
                $cartItemPrice+=$item->extras()->findOrFail($value)->price;
                $theElement.=$value." -- ".$item->extras()->findOrFail($value)->name."  --> ". $cartItemPrice." ->- ";
            }


            Cart::add((new \DateTime())->getTimestamp(), $cartItemName, $cartItemPrice, $request->quantity, array('id'=>$item->id,'variant'=>$request->variantID, 'extras'=>$request->extras,'restorant_id'=>$restID,'image'=>$item->icon,'friendly_price'=>  Money($cartItemPrice, env('CASHIER_CURRENCY','usd'),env('DO_CONVERTION',true))->format() ));

            return response()->json([
                'status' => true,
                'errMsg' => $theElement
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errMsg' => __("You can't add items from other restaurant!")
            ]);
            //], 401);
        }
    }

    public function getContent(){
        //Cart::clear();
        return response()->json([
            'data' => Cart::getContent(),
            'total' => Cart::getSubTotal(),
            'status' => true,
            'errMsg' => ''
        ]);
    }

    public function minutesToHours($numMun){
        $h =(int) ($numMun/60);
        $min=$numMun%60;
        if($min<10){
            $min="0".$min;
        }

        $time=$h.":".$min;
        if(env('TIME_FORMAT',"24hours")=="AM/PM"){
            $time=date("g:i A", strtotime($time));
        }
        return $time;
    }


    /*"0_from" => "09:00"
  "0_to" => "20:00"
  "1_from" => "09:00"
  "1_to" => "20:00"
  "2_from" => "09:00"
  "2_to" => "20:00"
  "3_from" => "09:00"
  "3_to" => "20:00"
  "4_from" => "09:00"
  "4_to" => "20:00"
  "5_from" => "09:00"
  "5_to" => "17:00"
  "6_from" => "09:00"
  "6_to" => "17:00"*/

  /*
    "0_from" => "9:00 AM"
  "0_to" => "8:10 PM"
  "1_from" => "9:00 AM"
  "1_to" => "8:00 PM"
  "2_from" => "9:00 AM"
  "2_to" => "8:00 PM"
  "3_from" => "9:00 AM"
  "3_to" => "8:00 PM"
  "4_from" => "9:00 AM"
  "4_to" => "8:00 PM"
  "5_from" => "9:00 AM"
  "5_to" => "5:00 PM"
  "6_from" => "9:00 AM"
  "6_to" => "5:00 PM"
   */

    public function getMinutes($time){
        $parts=explode(':',$time);
        return ((int)$parts[0])*60+(int)$parts[1];
    }



    public function getTimieSlots($hours){

        $ourDateOfWeek=date('N')-1;
        $restaurantOppeningTime=$this->getMinutes(date("G:i", strtotime($hours[$ourDateOfWeek."_from"])));
        $restaurantClosingTime=$this->getMinutes(date("G:i", strtotime($hours[$ourDateOfWeek."_to"])));


        //Interval
        $intervalInMinutes=env('DELIVERY_INTERVAL_IN_MINUTES',30);

        //Generate thintervals from
        $currentTimeInMinutes= Carbon::now()->diffInMinutes(Carbon::today());
        $from= $currentTimeInMinutes>$restaurantOppeningTime?$currentTimeInMinutes:$restaurantOppeningTime;//Workgin time of the restaurant or current time,



        //print_r('now: '.$from);
        //To have clear interval
        $missingInterval=$intervalInMinutes-($from%$intervalInMinutes); //21

        //print_r('<br />missing: '.$missingInterval);

        //Time to prepare the order in minutes
        $timeToPrepare=env('TIME_TO_PREPARE_ORDER_IN_MINUTES',0); //30

        //First interval
        $from+= $timeToPrepare<=$missingInterval?$missingInterval:($intervalInMinutes-(($from+$timeToPrepare)%$intervalInMinutes))+$timeToPrepare;

        //$from+=$missingInterval;

        //Generate thintervals to
        $to= $restaurantClosingTime;//Closing time of the restaurant or current time


        $timeElements=[];
        for ($i=$from; $i <= $to ; $i+=$intervalInMinutes) {
            array_push($timeElements,$i);
        }
        //print_r("<br />");
        //print_r($timeElements);



        $slots=[];
        for ($i=0; $i < count($timeElements)-1 ; $i++) {
            array_push($slots,[$timeElements[$i],$timeElements[$i+1]]);
        }

        //print_r("<br />SLOTS");
        //print_r($slots);


        //INTERVALS TO TIME
        $formatedSlots=[];
        for ($i=0; $i < count($slots) ; $i++) {
            $key=$slots[$i][0]."_".$slots[$i][1];
            $value=$this->minutesToHours($slots[$i][0])." - ".$this->minutesToHours($slots[$i][1]);
            $formatedSlots[$key]=$value;
            //array_push($formatedSlots,[$key=>$value]);
        }



        return($formatedSlots);


    }

    public function getRestorantHours($restorantID){
          //Create all the time slots
          //The restaurant
          $restaurant=Restorant::findOrFail($restorantID);
          //dd($restaurant->hours);

          $timeSlots=$restaurant->hours?$this->getTimieSlots($restaurant->hours->toArray()):[];

          //Modified time slots for app
          $timeSlotsForApp=[];
          foreach ($timeSlots as $key => $timeSlotsTitle) {
             array_push($timeSlotsForApp,array('id'=>$key,'title'=>$timeSlotsTitle));
          }

          //Working hours
          $ourDateOfWeek=date('N')-1;

          $format="G:i";
          if(env('TIME_FORMAT',"24hours")=="AM/PM"){
              $format="g:i A";
          }

          //dd($ourDateOfWeek);
          dd($restaurant->hours[$ourDateOfWeek."_from"]);

          $openingTime=date($format, strtotime($restaurant->hours[$ourDateOfWeek."_from"]));
          $closingTime=date($format, strtotime( $restaurant->hours[$ourDateOfWeek."_to"]));

          $params = [
            'restorant' => $restaurant,
            'timeSlots' => $timeSlotsForApp,
            'openingTime' => $restaurant->hours&&$restaurant->hours[$ourDateOfWeek."_from"]?$openingTime:null,
            'closingTime' => $restaurant->hours&&$restaurant->hours[$ourDateOfWeek."_to"]?$closingTime:null,
         ];

         if($restaurant){
            return response()->json([
                'data' => $params,
                'status' => true,
                'errMsg' => ''
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errMsg' => 'Restorants not found!'
            ]);
        }

    }





    public function cart(){
        $restorantID=null;
        foreach (Cart::getContent() as $key => $cartItem) {
            $restorantID=$cartItem->attributes->restorant_id;
            break;
        }

        //The restaurant
        $restaurant=Restorant::findOrFail($restorantID);

        //Create all the time slots
        $timeSlots=$restaurant->hours?$this->getTimieSlots($restaurant->hours->toArray()):[];

        //Working hours
        $ourDateOfWeek=date('N')-1;

        $format="G:i";
        if(env('TIME_FORMAT',"24hours")=="AM/PM"){
            $format="g:i A";
        }


        $openingTime = $restaurant->hours ? date($format, strtotime($restaurant->hours[$ourDateOfWeek."_from"])) : null;
        $closingTime = $restaurant->hours ? date($format, strtotime($restaurant->hours[$ourDateOfWeek."_to"])) : null;

        //user addresses
        $addresses=[];
        if(config('app.isft')){
            $addresses=$this->getAccessibleAddresses($restaurant,auth()->user()->addresses->reverse());
        }


        $tables=Tables::where('restaurant_id',$restaurant->id)->get();
        $tablesData=[];
        foreach ($tables as $key => $table) {
            $tablesData[$table->id]=$table->restoarea?$table->restoarea->name." - ".$table->name:$table->name;
        }

        $params = [
            'title' => 'Shopping Cart Checkout',
            'tables' =>  ['ftype'=>'select','name'=>"",'id'=>"table_id",'placeholder'=>"Select table",'data'=>$tablesData,'required'=>true],
            'restorant' => $restaurant,
            'timeSlots' => $timeSlots,
            'openingTime' => $restaurant->hours&&$restaurant->hours[$ourDateOfWeek."_from"]?$openingTime:null,
            'closingTime' => $restaurant->hours&&$restaurant->hours[$ourDateOfWeek."_to"]?$closingTime:null,
            'addresses' => $addresses
        ];

        //Open for all
        return view('cart')->with($params);
    }



    public function clear(Request $request){

        //Get the client_id from address_id

        $oreder = new Order;
        $oreder->address_id = strip_tags($request->addressID);
        $oreder->restorant_id = strip_tags($request->restID);
        $oreder->client_id = auth()->user()->id;
        $oreder->driver_id = 2;
        $oreder->delivery_price = 3.00;
        $oreder->order_price = strip_tags($request->orderPrice);
        $oreder->comment = strip_tags($request->comment);
        $oreder->save();

        foreach (Cart::getContent() as $key => $item) {
            $oreder->items()->attach($item->id);
        }

        //Find first status id,
        ///$oreder->stauts()->attach($status->id,['user_id'=>auth()->user()->id]);
        Cart::clear();
        return redirect()->route('front')->withStatus(__('Cart clear.'));
        //return back()->with('success',"The shopping cart has successfully beed added to the shopping cart!");;
    }


    /**

     * Create a new controller instance.

     *

     * @return void

     */

    public function remove(Request $request){
        Cart::remove($request->id);
        return response()->json([
            'status' => true,
            'errMsg' => ''
        ]);
    }

    /**
     * Makes general api resonse
     */
    private function generalApiResponse(){
        return response()->json([
            'status' => true,
            'errMsg' => ''
        ]);
    }

    /**
     * Updates cart
     */
    private function updateCartQty($howMuch,$item_id){
        Cart::update($item_id, array('quantity' => $howMuch));
        return $this->generalApiResponse();
    }


    /**
     * Increase cart
     */
    public function increase($id){
       return $this->updateCartQty(1,$id);
    }

    /**
     * Decrese cart
     */
    public function decrease($id){
        return $this->updateCartQty(-1,$id);
    }

}

