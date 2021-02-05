<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Restorant;
use App\Order;
use App\Address;
use App\Items;
use App\Status;
use App\Hours;
use App\City;
use Cart;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;


use Laravel\Cashier\Exceptions\PaymentActionRequired;
use App\Notifications\OrderNotification;

class ChefController extends Controller
{   

    protected $imagePath='uploads/restorants/';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->user()->hasRole('admin')){

            return view('clients.index', [
                    'clients' => User::role('client')->where(['active'=>1])->paginate(15),
                ]
            );
        }else return redirect()->route('orders.index')->withStatus(__('No Access'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $client)
    {
        if(auth()->user()->hasRole('admin')){
            return view('clients.edit', compact('client'));
        }else return redirect()->route('orders.index')->withStatus(__('No Access'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $client)
    {
        $client->active=0;
        $client->save();

        return redirect()->route('clients.index')->withStatus(__('Client successfully deleted.'));
    }

    /**
     * This is use to get Restorants
     *
     * @param  int $city_id
     * @return \Illuminate\Http\Response
     */
    public function getRestorants($city_id="none")
    {
        if($city_id=="none"){
            $restorants = Restorant::where(['active'=>1])->get();
        }else{
            $restorants = Restorant::where(['active'=>1])->where(['city_id'=>$city_id])->get();
        }
        

        if($restorants){
            return response()->json([
                'data' => $restorants,
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

    /**
     * This is use to get Restorants Items
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function getRestorantItems($id)
    {
        $restorant = Restorant::where(['id' => $id, 'active' => 1])->with(['categories.items.variants.extras'])->first();
        $items = [];
        if($restorant){
            if($restorant->categories){
                foreach($restorant->categories as $key => $category){
                    $theItemsInCategory=$category->items;
                    $catBox=[];
                    foreach ($theItemsInCategory as $key => $item) {
                        $itemObj=$item->toArray();
                        $itemObj['category_name']=$category->name;
                        $itemObj['extras']=$item->extras->toArray();
                        $itemObj['options']=$item->options->toArray();
                        $itemObj['variants']=$item->variants->toArray();
                        array_push($catBox,$itemObj);

                    }
                    array_push($items,$catBox);
                }

                return response()->json([
                    'data' => $items,
                    'status' => true,
                    'errMsg' => ''
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'errMsg' => 'Restorant categories not found!'
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'errMsg' => 'Restorant not found!'
            ]);
        }
    }

    /**
     * This is use to get MyNotifications
     *
     * @return \Illuminate\Http\Response
     */
    public function getMyNotifications(){
        $client = User::where(['api_token' => $_GET['api_token']])->first();

        if($client==null){
            return response()->json([
                'status' => false,
                'errMsg' => 'Client not found!'
            ]);
        }

        return response()->json([
            'data' => $client->notifications,
            'status' => true,
            'errMsg' => ''
        ]);
    }

    /**
     * This is use to get MyOrders
     *
     * @return \Illuminate\Http\Response
     */
    public function getMyOrders()
    {
        $client = User::where(['api_token' => $_GET['api_token']])->first();//->with(['orders']);

        if($client==null){
            return response()->json([
                'status' => false,
                'errMsg' => 'Client not found!'
            ]);
        }

        //Get client orders
        $orders=Order::where("client_id",$client->id)->orderBy('created_at','DESC')->limit(50)->with(['restorant','status','items','address','driver'])->get();

        return response()->json([
            'data' => $orders,
            'status' => true,
            'errMsg' => ''
        ]);
    }

    /**
     * This is use to MyAddressesForRestaurtant
     * @param  int $restaurant_id
     *
     * @return \Illuminate\Http\Response
     */
    public function getMyAddressesForRestaurtant($restaurant_id){
        $restaurant = Restorant::findOrFail($restaurant_id);
        $address=$this->getAccessibleAddresses(User::where(['api_token' => $_GET['api_token']])->with(['addresses'])->first(),$restaurant);
        if(count( $address)==0){
            return response()->json([
                'data' =>  $address,
                'status' => true,
                'errMsg' => ''
            ]);
        }else{
            return response()->json([
                'data' => [],
                'status' => false,
                'message'=>"",
                'errMsg' => "You don't have any address, please add new one."
            ]);
        }
    }

    /**
     * This is use to getMyAddressesWithFees
     * @param  int $restaurant_id
     *
     * @return \Illuminate\Http\Response
     */
    public function getMyAddressesWithFees($restaurant_id)
    {
        $restaurant = Restorant::findOrFail($restaurant_id);
        $client = User::where(['api_token' => $_GET['api_token']])->with(['addresses'])->first();
        $addresses=$this->getAccessibleAddresses($restaurant,$client->addresses->reverse());

        if(!$client->addresses->isEmpty()){

            //For each clinet address calcualte the price

            $okAddress=[];
            foreach ($addresses as $key => $value) {
                array_push($okAddress,$value);
            }
            
            return response()->json([
                'data' => $okAddress,
                'status' => true,
                'errMsg' => ''
            ]);
        }else{
            return response()->json([
                'data' => [],
                'status' => false,
                'message'=>"",
                'errMsg' => "You don't have any address, please add new one."
            ]);
        }
    }

    /**
     * This is use to getMyAddresses
     *
     * @return \Illuminate\Http\Response
     */
    public function getMyAddresses()
    {
        $client = User::where(['api_token' => $_GET['api_token']])->with(['addresses'])->first();

        if(!$client->addresses->isEmpty()){

            //For each clinet address calcualte the price            
            return response()->json([
                'data' => $client->addresses,
                'status' => true,
                'errMsg' => ''
            ]);
        }else{
            return response()->json([
                'data' => [],
                'status' => false,
                'message'=>"",
                'errMsg' => "You don't have any address, please add new one."
            ]);
        }
    }

    /**
     * This is use to Create the address
     *
     * @return \Illuminate\Http\Response
     */
    public function makeAddress(Request $request)
    {
        $client = User::where(['api_token' => $request->api_token])->first();

        $address = new Address;
        $address->address = $request->address;
        $address->user_id = $client->id;
        $address->lat = $request->lat;
        $address->lng = $request->lng;
        $address->apartment = $request->apartment ?? $request->apartment;
        $address->intercom = $request->intercom ?? $request->intercom;
        $address->floor =  $request->floor ?? $request->floor;
        $address->entry = $request->entry ?? $request->entry;
        $address->save();

        return response()->json([
            'status' => true,
            'errMsg' => 'New address added successfully!'
        ]);
    }

    /**
     * This is use to delete the address
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAddress(Request $request)
    {
        $client = User::where(['api_token' => $request->api_token])->first();

        $address_to_delete = Address::where(['id' => $request->id])->first();

        if($address_to_delete->user_id == $client->id){
            $address_to_delete->active=0;
            $address_to_delete->save();

            return response()->json([
                'status' => true,
                'errMsg' => 'Address successfully deactivated!'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errMsg' => 'You can`t delete this address!'
            ]);
        }
    }

    /**
     * This is use to get token
     *
     * @return \Illuminate\Http\Response
     */
    public function getToken(Request $request)
    {
        $user = User::where(['active'=>1,'email'=>$request->email])->first();
        if($user != null){
            if(Hash::check($request->password, $user->password)){
                if($user->hasRole(['client'])){
                    return response()->json([
                        'status' => true,
                        'token' => $user->api_token,
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ]);
                }else{
                    return response()->json([
                        'status' => false,
                        'errMsg' => 'User is not a client!'
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'errMsg' => 'Incorrect password!'
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'errMsg' => 'User not found. Incorrect email!'
            ]);
        }
    }

    /**
     * This is use to register the user
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        if($request->has('app_secret') && $request->app_secret == env('APP_SECRET')){

            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'unique:users', 'max:255'],
                'phone' => ['required', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
                'password' => ['required', 'string', 'min:6'],

                'city' => ['required', 'string', 'min:2'],
                'postcode' => ['required', 'integer'],
                'address' => ['required', 'string'/*, 'min:8'*/],
                'service_type' => ['required', 'integer'],
                
                'is_cooking_passionate' => ['required', 'integer'],
                'hours_from' => ['required', 'string'],
                'hours_to' => ['required', 'string'],
                // 'certificate' => ['required', 'integer'],
                

            ]);
            //dd($validator->errors()->messages());

            if(!$validator->fails()) {

                $chef = new User;

                $chef->name = $request->name;
                $chef->email = $request->email;
                $chef->phone = $request->phone;
                $chef->password = Hash::make($request->password);
                $chef->api_token = Str::random(80);
                

                //Assign role
                $chef->assignRole('owner');

                $chef->save();
                // add address
                $restorant = new Restorant;
                
                $restorant->name = $request->name;
                $restorant->subdomain = $this->makeAlias(strip_tags($request->name));
                $restorant->address = $request->address;
                $restorant->phone = $request->phone;
                
                $restorant->city_id = City::firstOrCreate(['name' => $request->city,
                'alias' => substr($request->city, 2)
                ])->id;

                $address = new Address;
                $address->postcode = $request->postcode;
                $address->user_id = $chef->id;
                $address->save();

                    
                $restorant->user_id = $chef->id;
                
                $restorant->can_pickup = $request->service_type;
                $restorant->can_deliver = !$request->service_type;
                $restorant->is_cooking_passionate = $request->is_cooking_passionate;
                
                if($request->hasFile('certificate')){
                    $restorant->certificate=$this->saveImageVersions(
                        $this->imagePath,
                        $request->certificate,
                        [
                            ['name'=>'large','w'=>590,'h'=>400],
                            ['name'=>'medium','w'=>295,'h'=>200],
                            ['name'=>'thumbnail','w'=>200,'h'=>200]
                        ]
                    );
                }

                if($request->hasFile('logo')){
                    $restorant->logo=$this->saveImageVersions(
                        $this->imagePath,
                        $request->logo,
                        [
                            ['name'=>'large','w'=>590,'h'=>400],
                            ['name'=>'medium','w'=>295,'h'=>200],
                            ['name'=>'thumbnail','w'=>200,'h'=>200]
                        ]
                    );
                }

                $restorant->save();

                $hours = new Hours;
                $hours['0_from'] = $request->hours_from;
                $hours['0_to'] = $request->hours_to;
                $hours['1_from'] = $request->hours_from;
                $hours['1_to'] = $request->hours_to;
                $hours['2_from'] = $request->hours_from;
                $hours['2_to'] = $request->hours_to;
                $hours['3_from'] = $request->hours_from;
                $hours['3_to'] = $request->hours_to;
                $hours['4_from'] = $request->hours_from;
                $hours['4_to'] = $request->hours_to;
                $hours['5_from'] = $request->hours_from;
                $hours['5_to'] = $request->hours_to;
                $hours['6_from'] = $request->hours_from;
                $hours['6_to'] = $request->hours_to;

                $hours->restorant_id = $restorant->id;
                
                $hours->save();


                // send email
            try{
                $randomOTPNumber = mt_rand(1000,9999);
                DB::table('users')
                    ->where('id', $chef->id)
                    ->update(['verification_code' => $randomOTPNumber]);

                $headers  = "From: " . "lr.testdemo@gmail.com" . "\r\n";
                $headers .= "Reply-To: ". "lr.testdemo@gmail.com" . "\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers = "Content-Type: text/html; charset=UTF-8";
                   
                $subject = "HomeCook Registration OTP";
                $msg  = "<p>Hello " . $chef->name . ",</p>";
                $msg .= "<p>Registration OTP is <b>" . $randomOTPNumber . ",</b></p>";
                $msg .= "<p>Thanks & Regards,</p>";
                $msg .= "Team HomeCook";
                //mail("lr.testdemo@gmail.com", $subject, $msg, $headers);
                mail($request->email, $subject, $msg, $headers);

                return response()->json([
                    'status' => true,
                    'succMsg' => 'Sent OTP into your email ' . $request->email
                ]);

            } catch(Exceptions $e) {
                // error_log($e);
                $subject = "Error In HomeCook Registration OTP";
                $fourRandomDigit = mt_rand(1000,9999);
                $msg = $e;
                mail("lr.testdemo@gmail.com", $subject, $msg);
                return response()->json([
                    'status' => false,
                    'errMsg' => 'We are sorry for server issue, Please wait for sometime and
                     send Registration OTP again'
                ]);
            } 

                return response()->json([
                    'status' => true,
                    'token' => $chef->api_token,
                    'id' => $chef->id
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'errMsg' => $validator->errors()
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'errMsg' => 'APP_SECRET missing or incorrect!'
            ]);
        }
    }

    /**
     * This is use to login by using Facebook
     * @param  int $restaurant_id
     *
     * @return \Illuminate\Http\Response
     */
    public function loginFacebook(Request $request)
    {
        if($request->has('app_secret') && $request->app_secret == env('APP_SECRET')){

            $client = User::where('email', $request->email)->first();

            if(!$client){
                $client = new User;
                $client->fb_id = $request->fb_id;
                $client->name = $request->name;
                $client->email = $request->email;
                $client->api_token = Str::random(80);
                $client->save();

                $client->assignRole('client');

            }else{
                if(empty($client->fb_id)){
                    $client->fb_id = $request->fb_id;
                }

                $client->update();
            }

            return response()->json([
                'status' => true,
                'token' => $client->api_token,
                'id' => $client->id,
                'msg' => 'Client logged in!'
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errMsg' => 'APP_SECRET missing or incorrect!'
            ]);
        }
    }

    /**
     * This is use to login by Google
     *
     * @return \Illuminate\Http\Response
     */
    public function loginGoogle(Request $request)
    {
        if($request->has('app_secret') && $request->app_secret == env('APP_SECRET')){

            $client = User::where('email', $request->email)->first();

            if(!$client){
                $client = new User;
                $client->google_id = $request->google_id;
                $client->name = $request->name;
                $client->email = $request->email;
                $client->api_token = Str::random(80);
                $client->save();

                $client->assignRole('client');
            }else{
                if(empty($client->google_id)){
                    $client->google_id = $request->google_id;
                }

                $client->update();
            }

            return response()->json([
                'status' => true,
                'token' => $client->api_token,
                'id' => $client->id,
                'msg' => 'Client logged in!'
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errMsg' => 'APP_SECRET missing or incorrect!'
            ]);
        }
    }

    /**
     * This is use to make a new order
     *
     * @return \Illuminate\Http\Response
     */
    public function makeOrder(Request $request)
    {

        $deliverMethod=$request->delivery_method;

        //TO-DO orderprice
        $orderPrice = $request->order_price;


        //Price without deliver
        $priceWithoutDelvier= $deliverMethod=="delivery"?$orderPrice-config('global.delivery'):$orderPrice;

        //Find client
        $client = User::where(['api_token' => $request->api_token])->first();

        //Try payment
        $srtipe_payment_id=null;
        if($request->payment_method == "stripe"){
            //Make the payment
            $total_price=(int)($orderPrice*100);
            try {
                Stripe::setApiKey(env('STRIPE_SECRET'));

                $customer = Customer::create(array(
                    'email' =>$client->email,
                    'source'  => $request->stripe_token
                ));

                $charge = Charge::create(array(
                    'customer' => $customer->id,
                    'amount'   => $total_price,
                    'currency' => env('CASHIER_CURRENCY','usd')
                ));
                $srtipe_payment_id=$charge->id;

            } catch (PaymentActionRequired $e) {
                //return redirect()->route('cart.checkout')->withError('The payment attempt failed because additional action is required before it can be completed.')->withInput();
                return response()->json([
                    'status' => false,
                    'errMsg' => 'The payment attempt failed because additional action is required before it can be completed.'
                ]);
            }
        }

        //Fees
        $restorant_fee = Restorant::select('fee', 'static_fee')->where(['id'=> $request->restaurant_id])->get()->first();
        //Commision fee
        //$restorant_fee = Restorant::select('fee')->where(['id'=>$restorant_id])->value('fee');
        $order_fee = ($restorant_fee->fee / 100) * $priceWithoutDelvier;

        //Make order
        $order = new Order;
        if($deliverMethod=="delivery"){
            $order->address_id = $request->address_id;
        }

        if($request->payment_method == "stripe"){
            $order->srtipe_payment_id= $srtipe_payment_id;
            $order->payment_status="paid";
        }

        $order->delivery_method=$deliverMethod=="delivery"?1:0;
        $order->delivery_pickup_interval=$request->timeslot;

        $order->restorant_id = $request->restaurant_id;
        $order->client_id = $client->id;
        $order->delivery_price = $deliverMethod=="delivery"?config('global.delivery'):0;
        $order->order_price = $priceWithoutDelvier;
        $order->comment = $request->comment ? $request->comment."" : "";
        $order->payment_method = $request->payment_method;

        $order->fee = $restorant_fee->fee;
        $order->fee_value = $order_fee;
        $order->static_fee = $restorant_fee->static_fee;

        //$order->srtipe_payment_id = $request->payment_method == "stripe" ? $payment_stripe->id : null;
        //$order->payment_status = $request->payment_method == "stripe" ? 'paid' : 'unpaid';
        $order->save();

        //Create status
        $status = Status::find(1);
        $order->status()->attach($status->id,['user_id' => $client->id, 'comment' => ""]);

        //If approve directly
        if(config('app.order_approve_directly')){
            $status = Status::find(2);
            $order->status()->attach($status->id,['user_id'=>1,'comment'=>__('Automatically apprved by admin')]);
        }

        //Create items
        foreach($request->items as $key => $item) {
            $order->items()->attach($item['id'], ['qty' => $item['qty']]);
        }

        $restorant = Restorant::findOrFail($request->restaurant_id);
        $restorant->user->notify(new OrderNotification($order));

        return response()->json([
            'status' => true,
            'errMsg' => 'Order created.'
        ]);
    }

    /**
     * This is use to get settings
     *
     * @return \Illuminate\Http\Response
     */
    public function getSettings(Request $request)
    {
        if($request->has('app_secret') && $request->app_secret == env('APP_SECRET')){
            return response()->json([
                'data' => [
                    'SITE_NAME' => config('global.site_name'),
                    'SITE_DESCRIPTION' => config('global.description'),
                    'HEADER_TITLE' => config('global.header_title'),
                    'HEADER_SUBTITLE' => config('global.header_subtitle'),
                    'CURRENCY' => config('global.currency'),
                    'DELIVERY' => config('global.delivery'),
                    'FACEBOOK' => config('global.facebook'),
                    'INSTRAGRAM' => config('global.instagram'),
                    'PLAY_STORE' => config('global.playstore'),
                    'APP_STORR' => config('global.appstore'),
                    'MOBILE_INFO_TITLE' => config('global.mobile_info_title'),
                    'MOBILE_INFO_SUBTITLE' => config('global.mobile_info_subtitle'),
                    'HIDE_CODE' => env('HIDE_COD') ? env('HIDE_COD') : false,
                    'ENABLE_STRIPE' => env('ENABLE_STRIPE') ? env('ENABLE_STRIPE') : false,
                    'STRIPE_KEY' => env('STRIPE_KEY') ? env('STRIPE_KEY') : "",
                    'STRIPE_SECRET' => env('STRIPE_SECRET') ? env('STRIPE_SECRET') : "",
                    'ENABLE_STRIPE_IDEAL' => env('ENABLE_STRIPE_IDEAL') ? env('ENABLE_STRIPE_IDEAL') : false,
                    'DEFAULT_PAYMENT' => env('DEFAULT_PAYMENT') ? env('DEFAULT_PAYMENT') : "",
                    'CASHIER_CURRENCY' => env('CASHIER_CURRENCY') ? env('CASHIER_CURRENCY') : "",
                    'GOOGLE_MAPS_API_KEY' => env('GOOGLE_MAPS_API_KEY') ? env('GOOGLE_MAPS_API_KEY') : "",
                    'GOOGLE_CLIENT_ID' => env('GOOGLE_CLIENT_ID') ? env('GOOGLE_CLIENT_ID') : "",
                    'GOOGLE_CLIENT_SECRET' => env('GOOGLE_CLIENT_SECRET') ? env('GOOGLE_CLIENT_SECRET') : "",
                    'FACEBOOK_CLIENT_ID' => env('FACEBOOK_CLIENT_ID') ? env('FACEBOOK_CLIENT_ID') : "",
                    'FACEBOOK_CLIENT_SECRET' => env('FACEBOOK_CLIENT_SECRET') ? env('FACEBOOK_CLIENT_SECRET') : "",
                    'SINGLE_MODE'=>env('SINGLE_MODE') ? env('SINGLE_MODE') : false, 
                    'SINGLE_MODE_ID'=>env('SINGLE_MODE_ID') ? env('SINGLE_MODE_ID') : 1
                ],
                'status' => true,
                'errMsg' => ''
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errMsg' => 'APP_SECRET missing or incorrect!'
            ]);
        }
    }

    /**
     * This is use to get User Data
     *
     * @return \Illuminate\Http\Response
     */
    public function getUseData()
    {
        $user = User::where(['api_token' => $_GET['api_token']])->first();

        if($user){
            return response()->json([
                'status' => true,
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ? $user->phone : ""
                ],
                'msg' => 'User found!'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errMsg' => 'User not found!'
            ]);
        }
    }

    /**
     * This is use to get the list of required orders
     *
     * Requested Order
     * Running Order
     * Done Order
     * Cancel Order
     *
     * @return \Illuminate\Http\Response
     */
    public function orderlist(Request $request){

        $user = User::where(['api_token' => $request->api_token])->first();
        if($user){

            $restorantId = $user->id;

            $items = $this->getOrderList($restorantId, $request->order_type);

            return response()->json([
                'data' => $items,
                'dataCount' => count($items),
                'status' => true,
                'succMsg' => 'Get all order data successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errMsg' => 'Invalid token'
            ]);
        }
    }

    /**
     * This is use change the status of order
     *
     * Chef will change the status of order
     * Accepted order
     * Done Order
     * Cancel Order
     *
     * @return \Illuminate\Http\Response
     */
    public function changeorderstatus(Request $request){

        $user = User::where(['api_token' => $request->api_token])->first();
        if($user){

            switch ($request->order_status) {
                case 'accept':
                        $alias = 'accepted_by_restaurant';
                    break;
                case 'done':
                        $alias = 'closed';
                    break;                
                case 'cancel':
                        $alias = 'rejected_by_restaurant';
                    break;                
                default:
                    $alias = 'just_created';
                    break;
            }

            $orders = Order::where(['id' => $request->order_id])->get()->first();

            $status = Status::select('id', 'alias')->where(['alias' => $alias])->get()->first();
            
            if(!empty($status)){
                // order_has_status table having user_id and this user id is client_id ( customr id not ChefId) 
                DB::table('order_has_status')
                        //->where(['order_id' => $request->order_id, 'user_id' => $orders->client_id])
                        ->where(['order_id' => $request->order_id])
                        ->update(['status_id' => $status->id]);
                return response()->json([
                    'status' => true,
                    'succMsg' => 'Order status change successfully'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'succMsg' => 'Order not found for this user'
                ]);
            }

        } else {
            return response()->json([
                'status' => false,
                'errMsg' => 'Invalid token'
            ]);
        }
    }

    /**
     * This is use for display chef dashboard view
     *
     * Running Order Count
     * Requested Order Count
     * Total Revenue
     * Revenue Graph
     * Rating count and average
     * Popuar Items List
     *
     * @return \Illuminate\Http\Response
     */
    public function chefdashboardview(Request $request) {
        $user = User::where(['api_token' => $request->api_token])->first();
        if($user)
        {
            // user id is chef id
            $user_id = $user->id;
            $runing_order = $this->getOrderList($user_id, "runing_order");
            $runing_order_count = count($runing_order);

            $requested_order = $this->getOrderList($user_id, "requested_order");
            $requested_order_count = count($requested_order);
            
            $revenue = DB::select("SELECT sum(order_price) AS revenue FROM `orders` WHERE `restorant_id`='".$user_id."' GROUP BY restorant_id");
            $total_revenue = number_format(0, 2);
            if($revenue)
            {
                $total_revenue = number_format($revenue[0]->revenue, 2);
            }
            //  AND created_at >= (DATE(NOW()) - INTERVAL 7 DAY)
            $revenue_list = DB::select("SELECT ROUND(sum(order_price), 2) AS revenue, COUNT(id) AS total_count, DATE_FORMAT(created_at, '%h%p') AS added_date FROM `orders` WHERE `restorant_id`='".$user_id."' GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d %H') ORDER BY created_at ASC");

            $reviews = DB::select("SELECT ROUND(IFNULL(AVG(rating), 0), 2) AS rating_average, COUNT(id) AS rating_count FROM ratings WHERE rateable_id='".$user_id."'");
            $review_data['rating_average'] = $reviews[0]->rating_average;
            $review_data['rating_count'] = $reviews[0]->rating_count;

            //  AND o.created_at >= (DATE(NOW()) - INTERVAL 7 DAY)
            $popular_items = DB::select("SELECT SUM(ohi.qty) sale_count, i.id, i.image, i.name, i.price, i.vat FROM orders AS o JOIN order_has_items AS ohi ON ohi.order_id=o.id JOIN items AS i ON i.id=ohi.item_id WHERE o.restorant_id='".$user_id."' GROUP BY ohi.item_id ORDER BY SUM(ohi.qty) DESC");

            $popularItemsWithImage = array();
            foreach ($popular_items as $key => &$popularItem) {
                $popularItem->image = Items::getImge($popularItem->image,str_replace("_large.jpg","_thumbnail.jpg",config('global.restorant_details_image')),"_thumbnail.jpg");
            }
      
            $data['total_runing_order'] = $runing_order_count;
            $data['total_requested_order'] = $requested_order_count;
            $data['total_revenue'] = $total_revenue;
            $data['revenue_list'] = $revenue_list;
            $data['reviews'] = $review_data;
            $data['popular_items'] = $popular_items;
            return response()->json([
                'status' => true,
                'data' => $data,
                'succMsg' => 'Dashboard Data fetched successfully'
            ]);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errMsg' => 'Invalid token'
            ]);
        }
    }

    /**
     * This is use get order list
     *
     * @param  int $restaurant_id
     * @param  string $order_type
     *
     * @return \Illuminate\Http\Response
     */
    public function getOrderList($restorantId, $order_type)
    {
        $orders = Order::orderBy('created_at','desc');
        $orders = $orders->where(['restorant_id' => $restorantId]);
        $dashboardOrderCount = $orders->get()->count();

        switch ($order_type) {
            case 'requested_order':
                    $alias = 'just_created';
                break;
            case 'runing_order':
                    $alias = 'accepted_by_restaurant';
                break;                
            case 'done_order':
                    $alias = 'closed';
                break;                
            case 'cancel_order':
                    $alias = 'rejected_by_restaurant';
                break;                
            default:
                $alias = 'just_created';
                break;
        }

        $items=array();
        foreach ($orders->get() as $key => $order) {
            if($order->status->pluck('alias')->last() == $alias) {
                $item=array(
                    "order_id"=>$order->id,
                    "chef_name"=>$order->restorant->name,
                    "chef_id"=>$order->restorant_id,
                    "created"=>$order->created_at,
                    "last_status"=>$order->status->pluck('alias')->last(),
                    "client_name"=>$order->client ?  $order->client->name : "",
                    "client_id"=>$order->client ? $order->client_id : null,
                    "table_name"=>$order->table ? $order->table->name : "",
                    "table_id"=>$order->table ? $order->table_id : null,
                    "area_name"=>$order->table && $order->table->restoarea ? $order->table->restoarea->name : "",
                    "area_id"=>$order->table && $order->table->restoarea ? $order->table->restoarea->id : null,
                    "address"=>$order->address ? $order->address->address : "",
                    "address_id"=>$order->address_id,
                    //"driver_name"=>$order->driver?$order->driver->name:"",
                    //"driver_id"=>$order->driver_id,
                    "order_value"=>$order->order_price,
                    "order_delivery"=>$order->delivery_price,
                    "order_total"=>$order->delivery_price+$order->order_price,
                    'payment_method'=>$order->payment_method,
                    'srtipe_payment_id'=>$order->srtipe_payment_id,
                    'item_name'=>'',
                    'item_image'=>'',
                    //'order_fee'=>$order->fee_value,
                    //'restaurant_fee'=>$order->fee,
                    //'restaurant_static_fee'=>$order->static_fee,
                    //'vat'=>$order->vatvalue
                );
                if($order->items->isNotEmpty())
                {
                    $item['item_name'] = $order->items[0]->name;
                    $item['item_image'] = $order->items[0]->icon;
                }
                array_push($items,$item);
            }
            
        }
        return $items;
    }

    /**
     * This is use for display chef revenue
     *
     * Total Revenue
     * Revenue Graph
     * Rating count and average
     * Popuar Items List
     *
     * @return \Illuminate\Http\Response
     */
    public function revenuelist(Request $request)
    {
        $user = User::where(['api_token' => $request->api_token])->first();
        if($user)
        {
            // user id is chef id
            $user_id = $user->id;
            
            $revenue = DB::select("SELECT sum(order_price) AS revenue FROM `orders` WHERE `restorant_id`='".$user_id."' GROUP BY restorant_id");
            $total_revenue = number_format(0, 2);
            if($revenue)
            {
                $total_revenue = number_format($revenue[0]->revenue, 2);
            }
            //  AND created_at >= (DATE(NOW()) - INTERVAL 7 DAY)
            $revenue_list = DB::select("SELECT ROUND(sum(order_price), 2) AS revenue, COUNT(id) AS total_count, DATE_FORMAT(created_at, '%h%p') AS added_date FROM orders WHERE restorant_id='".$user_id."' GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d %H') ORDER BY created_at ASC");

            $revenue_item_list = DB::select("SELECT i.id, i.name, i.image, c.name AS category_name, COUNT(ohi.item_id) AS item_sale_count FROM orders AS o JOIN order_has_items AS ohi ON ohi.order_id=o.id JOIN items AS i ON i.id=ohi.item_id JOIN categories AS c ON c.id=i.category_id WHERE o.restorant_id='".$user_id."' GROUP BY ohi.item_id");

            foreach ($revenue_item_list as $key => &$revenue_item) {
                $revenue_item->image = Items::getImge($revenue_item->image,str_replace("_large.jpg","_thumbnail.jpg",config('global.restorant_details_image')),"_thumbnail.jpg");
            }
            $data = array();
            $data['total_revenue'] = $total_revenue;
            $data['revenue_list'] = $revenue_list;
            $data['item_list'] = $revenue_item_list;
            return response()->json([
                'status' => true,
                'data' => $data,
                'succMsg' => 'Revenue list found successfully.'
            ]);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errMsg' => 'Invalid token'
            ]);
        }
    }

    /**
     * This is use to reset the password
     * Flow:
     *
     * 1. User enter forgot password email
     * 2. User got OTP in mail
     * 3. User come into Reset password screen after login
     *
     * @return \Illuminate\Http\Response
     */
    public function resetpassword(Request $request)
    {
        $user = User::where(['email' => $request->email])->first();
        if($user)
        {
            DB::table('users')
                    ->where(['email' => $request->email])
                    ->update(['password' => Hash::make($request->password)]);
            return response()->json([
                'status' => true,
                'succMsg' => 'Password Changed successfully.'
            ]);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errMsg' => 'Invalid email'
            ]);
        }
    }

    /**
     * This is use for display chef review/rating list
     *
     * reviews
     * review_count
     * @return \Illuminate\Http\Response
     */
    public function reviewlist(Request $request)
    {
        $user = User::where(['api_token' => $request->api_token])->first();
        if($user)
        {
            // user id is chef id
            $user_id = $user->id;

            $review_count = DB::select("SELECT ROUND(IFNULL(AVG(rating), 0), 2) AS rating_average, COUNT(id) AS rating_count FROM ratings WHERE rateable_id='".$user_id."'");
            $review_data['rating_average'] = $review_count[0]->rating_average;
            $review_data['rating_count'] = $review_count[0]->rating_count;

            $reviews = DB::select("SELECT DATE_FORMAT(r.created_at, '%d/%m/%Y') AS added_date, r.order_id, r.comment, r.rating, '' AS image FROM ratings AS r WHERE r.rateable_id='".$user_id."' ORDER BY r.created_at DESC, id DESC");
            // echo Storage::url();
            foreach($reviews as &$review)
            {
                $image_url = url('/uploads/settings/no-image.png');
                $review->image = $image_url;
            }
            $data = array();
            $data['reviews'] = $reviews;
            $data['review_count'] = $review_data;
            return response()->json([
                'status' => true,
                'data' => $data,
                'succMsg' => 'Reviews found successfully.'
            ]);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errMsg' => 'Invalid token'
            ]);
        }
    }

    /**
     * This is use for get user profile information
     * 
     * @return \Illuminate\Http\Response
     */
    public function userprofile(Request $request)
    {
        $user = User::where(['api_token' => $request->api_token])->first();
        if($user)
        {
            $hours = DB::select("SELECT h.* FROM hours AS h JOIN restorants AS r ON r.id=h.restorant_id JOIN users AS u ON u.id=r.user_id WHERE u.id='".$user->id."'");
            $start_time = "0_from";
            $end_time = "0_to";
            $data = array();
            $data['id'] = $user->id;
            $data['name'] = $user->name;
            $data['email'] = $user->email;
            $data['phone'] = $user->phone;
            $data['service_time'] = "Mon-Sun ".(date("h:i A", strtotime($hours[0]->$start_time)))." - ".(date("h:i A", strtotime($hours[0]->$end_time)));
            return response()->json([
                'status' => true,
                'data' => $data,
                'succMsg' => 'User Profile found successfully.'
            ]);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errMsg' => 'Invalid token'
            ]);
        }
    }

    /**
     * This is use for get user profile information
     * 
     * @return \Illuminate\Http\Response
     */
    public function updateuserprofile(Request $request)
    {
        $user = User::where(['api_token' => $request->api_token])->first();
        if($user)
        {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            ]);

            $user_email_exist = User::where(['email' => $user->email])->whereNotIn('id', [$user->id])->first();
            if($user_email_exist)
            {
                return response()->json([
                    'status' => true,
                    'errMsg' => 'This email id is not available.'
                ]);
            }
            else
            {

                $restorant = Restorant::where(['user_id'=>$user->id])->first();
                $hours = Hours::where(['restorant_id'=>$restorant->id])->first();

                DB::table('users')
                    ->where(['id' => $user->id])
                    ->update(['name' => $request->name, 'email' => $request->email, 'phone' => $request->phone]);

                DB::table('restorants')
                    ->where(['id' => $restorant->id])
                    ->update(['name' => $request->name, 'phone' => $request->phone]);

                DB::table('hours')
                    ->where(['id' => $hours->id])
                    ->update([
                                '0_from'=>$request->hours_from, '0_to'=>$request->hours_to,
                                '1_from'=>$request->hours_from, '1_to'=>$request->hours_to,
                                '2_from'=>$request->hours_from, '2_to'=>$request->hours_to,
                                '3_from'=>$request->hours_from, '3_to'=>$request->hours_to,
                                '4_from'=>$request->hours_from, '4_to'=>$request->hours_to,
                                '5_from'=>$request->hours_from, '5_to'=>$request->hours_to,
                                '6_from'=>$request->hours_from, '6_to'=>$request->hours_to,
                                'updated_at'=>date("Y-m-d H:i:s")
                            ]);

                $updated_user = User::where(['id' => $user->id])->first();
                $data = array();
                $data['name'] = $updated_user->name;
                $data['email'] = $updated_user->email;
                $data['phone'] = $updated_user->phone;
                return response()->json([
                    'status' => true,
                    'data' => $data,
                    'succMsg' => 'User Profile found successfully.'
                ]);
            }
        }
        else
        {
            return response()->json([
                'status' => false,
                'errMsg' => 'Invalid token'
            ]);
        }
    }
    
}
