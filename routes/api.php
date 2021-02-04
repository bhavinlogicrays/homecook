<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'auth:api'], function () {
    //Chef
    Route::get('/driverorders', 'DriverController@getOrders')->name('driver.orders');
    Route::get('/updateorderstatus/{order}/{status}', 'DriverController@updateOrderStatus')->name('driver.updateorderstatus');
    Route::get('/updateorderlocation/{order}/{lat}/{lng}', 'DriverController@orderTracking')->name('driver.updateorderlocation');
    Route::get('/rejectorder/{order}','DriverController@rejectOrder')->name('driver.rejectorder');
    Route::get('/acceptorder/{order}','DriverController@acceptOrder')->name('driver.acceptorder');
    Route::get('/driveronline','DriverController@goOnline')->name('driver.goonline');
    Route::get('/drveroffline','DriverController@goOffline')->name('driver.gooffline');


    //Client
    Route::get('/myorders', 'ClientController@getMyOrders');
    Route::get('/mynotifications', 'ClientController@getMyNotifications');
    Route::get('/myaddresses', 'ClientController@getMyAddresses');
    Route::get('/myaddresseswithfees/{restaurant_id}', 'ClientController@getMyAddressesWithFees');
    Route::post('/make/order','ClientController@makeOrder')->name('make.order');
    Route::post('/make/address','ClientController@makeAddress')->name('make.address');
    Route::post('/delete/address','ClientController@deleteAddress')->name('delete.address');
    Route::get('/user/data', 'ClientController@getUseData')->name('user.getData');
});

// Driver
Route::post('/drivergettoken', 'DriverController@getToken')->name('driver.getToken');
Route::post('/driver/register', 'DriverController@register')->name('client.register');

// Client
Route::post('/login', 'ClientController@getToken')->name('client.getToken');
Route::post('/client/register', 'ClientController@register')->name('client.register');
Route::post('/client/loginfb','ClientController@loginFacebook');
Route::post('/client/logingoogle','ClientController@loginGoogle');
Route::get('/restorantslist/{city_id}', 'ClientController@getRestorants')->name('restorants.list');
Route::get('/citieslist', 'CitiesController@getCities')->name('cities.list');
Route::get('/restorant/{id}/items', 'ClientController@getRestorantItems')->name('restorant.items');
Route::get('/restaurant/{restorants}/hours', 'CartController@getRestorantHours')->name('restorant.hours');
Route::get('/deliveryfee/{res}/{adr}', 'SettingsController@getDeliveryFee')->name('delivery.fee');

Route::post('/app/settings','ClientController@getSettings')->name('app.settings');

// Chef
Route::post('/forgot', 'ClientController@forgot')->name('client.forgot');
Route::post('/verificationcode', 'ClientController@verificationcode')->name('client.verificationcode');
Route::post('/chef/register', 'ChefController@register')->name('chef.register');

Route::post('/orderlist', 'ChefController@orderlist')->name('chef.orderlist');
Route::post('/changeorderstatus', 'ChefController@changeorderstatus')->name('chef.changeorderstatus');

Route::post('/chefdashboardview', 'ChefController@chefdashboardview')->name('chef.chefdashboardview');
Route::post('/revenuelist', 'ChefController@revenuelist')->name('chef.revenuelist');
Route::post('/resetpassword', 'ChefController@resetpassword')->name('chef.resetpassword');
Route::post('/reviewlist', 'chefcontroller@reviewlist')->name('chef.reviewlist');

//Route::post('send-sms','SmsController@store');
//Route::post('verify-user','SmsController@verifyContact');