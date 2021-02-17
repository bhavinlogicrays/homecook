<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserNotifications;
use App\User;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = User::where(['api_token' => $request->api_token])->first();
        if($user)
        {
            $notifications = UserNotifications::where(['chef_id' => $user->id])->get();
            $data = array();
            if(!empty($notifications))
            {
                foreach($notifications as $notification)
                {
                    $notification->client->profile_pic = User::getImage($user->id,$notification->client->profile_pic,str_replace("_large.jpg","_thumbnail.jpg",config('global.restorant_details_image')),"_thumbnail.jpg");
                    $created_at = $this->getTimeAgo($notification->created_at);
                    $data[] = array("id"=>$notification->id, 'client_image'=>$notification->client->profile_pic, 'message'=>$notification->notification_message, 'type'=>$notification->notification_type, 'added'=>$created_at);
                }
                return response()->json([
                    'status' => true,
                    'data' => $data,
                    'succMsg' => 'Notification found successfully.'
                ]);
            }
            else
            {
                return response()->json([
                    'status' => true,
                    'data' => $data,
                    'succMsg' => 'No any notification yet.'
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
     * @param  \App\UserNotifications  $userNotifications
     * @return \Illuminate\Http\Response
     */
    public function show(UserNotifications $userNotifications)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\UserNotifications  $userNotifications
     * @return \Illuminate\Http\Response
     */
    public function edit(UserNotifications $userNotifications)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UserNotifications  $userNotifications
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserNotifications $userNotifications)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UserNotifications  $userNotifications
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserNotifications $userNotifications)
    {
        //
    }

    /**
     * Get the time ago value
     *
     * @param created_at - notification added date/time
     * @return 
     */
    public function getTimeAgo($created_at)
    {
        $added_time = strtotime($created_at);
        $etime = time() - $added_time;

        if ($etime < 1)
        {
            return '0 seconds';
        }

        $a = array( 365 * 24 * 60 * 60  =>  'year',
                     30 * 24 * 60 * 60  =>  'month',
                          24 * 60 * 60  =>  'day',
                               60 * 60  =>  'hour',
                                    60  =>  'minute',
                                     1  =>  'second'
                  );

        $a_plural = array('year' => 'years',
                          'month' => 'months',
                          'day' => 'days',
                          'hour' => 'hours',
                          'minute' => 'minutes',
                          'second' => 'seconds'
                         );

        foreach ($a as $secs => $str)
        {
            $d = $etime / $secs;
            if ($d >= 1)
            {
                $r = round($d);
                return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
            }
        }
    }
}
