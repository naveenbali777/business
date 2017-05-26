<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Users;
use App\Booking;
use App\BusinessUser;
use App\MobileVerification;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {      
        $user_id    = $request->userid;
        $profile_id  = $request->profileId;

        if(isset($user_id) && $user_id > 0)
        {
            $user_detail = Users::find($user_id);
            $name       = $user_detail->name;
            $email      = $user_detail->email;
            $phone      = $user_detail->phone;

            $PhoneVerication = $user_detail->where('phone_verify_status',1)->count();
            if($PhoneVerication <= 0){                    
                $res = array('status' => 0,'error-code'=>405, 'message' => "Sorry! User has not verifed phone");
                return response()->json($res);
            }

        }else{

            $name       = $request->bookingName;
            $email      = $request->bookingEmail;
            $phone      = $request->bookingPhone;

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              $res = array('status' => 0,'error-code'=>108, 'message' => "Invalid email format");
              return response()->json($res);
            } 

            $australian_phone = "/^(\+\d{2}[ \-]{0,1}){0,1}(((\({0,1}[ \-]{0,1})0{0,1}\){0,1}[2|3|7|8]{1}\){0,1}[ \-]*(\d{4}[ \-]{0,1}\d{4}))|(1[ \-]{0,1}(300|800|900|902)[ \-]{0,1}((\d{6})|(\d{3}[ \-]{0,1}\d{3})))|(13[ \-]{0,1}([\d \-]{5})|((\({0,1}[ \-]{0,1})0{0,1}\){0,1}4{1}[\d \-]{8,10})))$/";
            if (!preg_match($australian_phone,$phone)) 
            {
              $res = array('status' => 0,'error-code'=>109, 'message' => "Mobile is not Australian phone no.");
              return response()->json($res);
            }


        }

        if($name !="" && $email !="" && $phone !="" )
        {                
            if(isset($request->hours) && $request->hours > 0){
                $duration = $request->hours." ".$request->bookingDuration;
            }else{
                $duration = $request->bookingDuration;
            }
            $bst = date("Y-m-d H:i:s",strtotime(str_replace("/", "-",$request->bookingDate)." ".$request->bookingTime));
            
            $booking_count = 0 ;

            if($duration != 'Weekend' && $duration != 'Dinner date'){                    
                $bet  = date("Y-m-d H:i:s",(strtotime("+ ".$duration, strtotime($bst))));

                 $booking_count =  Booking::where('business_profile_id', $profile_id)
                ->whereRaw("(book_start_time <= '".$bst."' AND book_end_time >= '".$bst."'")
                ->orWhereRaw("book_start_time <= '".$bet."' AND book_end_time >= '".$bet."'")
                ->orWhereRaw("book_start_time >= '".$bst."' AND book_end_time <= '".$bet."')")
                ->count();
            }

            if($booking_count <= 0){
                $book = new Booking;
                $book->name                 = $name;
                $book->user_email           = $email;
                $book->phone                = $phone;
                $book->user_id              = $user_id;
                $book->business_profile_id  = $profile_id;
                $book->confirmation_way     = $request->confirmation;           
                $book->book_start_time      = $bst;
                $book->uses_time            = $duration;
                $book->in_out_call          = $request->bookingIncallOutcall;
                $book->hotel_name           = $request->hotelName;
                $book->room_no              = $request->roomNo;
                $book->outcall_address      = $request->outcallAddress;
                $book->additional_info      = $request->bookingAdditional;
                
                if($duration != 'Weekend' && $duration != 'Dinner date'){               
                    $book->book_end_time  = $bet;
                }

                $res = ($book->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Successfully booked") : array('status' => 0, 'error-code'=>100, 'message' => "Sorry! There was an error in booking");
                return response()->json($res);
            }else{
                $res =  array('status' => 0,'error-code'=>905, 'message' => "Booking is already for this time frame.") ;
                return response()->json($res);
            }
        }else if($name ==""){
            $res =  array('status' => 0,'error-code'=>103, 'message' => "Name is empty") ;
            return response()->json($res);
        }else if($email ==""){
            $res =  array('status' => 0,'error-code'=>105, 'message' => "Email is empty") ;
            return response()->json($res);
        }else if($phone ==""){
            $res =  array('status' => 0,'error-code'=>106, 'message' => "Mobile is empty") ;
            return response()->json($res);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $email      = $request->username;
        $password   = $request->pwd;

        if($email !="" && $password !="")
        {
            $whereThese = ['email' => $email, 'password' => md5($password)];
            $user = Users::where($whereThese);
            $userCount = $user->count();
            
            $code = str_random(64);
                    
            if($userCount > 0) {
                $where = ['email' => $email];
                $user = Users::where($where)->update(array('remember_token' => $code));                        
            }

            $res = ($userCount > 0) ? array('status' => 1,'error-code'=>0, 'message' => "User Found",'token' => $code) : array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");
            return response()->json($res);

        }else if($email ==""){
            $res =  array('status' => 0,'error-code'=>202, 'message' => "Email is empty") ;
            return response()->json($res);

        }else if($password ==""){
            $res =  array('status' => 0,'error-code'=>203, 'message' => "Password is empty") ;
            return response()->json($res);
        }
    }

    
}
