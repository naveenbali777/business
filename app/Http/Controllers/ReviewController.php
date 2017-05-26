<?php

namespace App\Http\Controllers;


use App\Users;
use App\Review;
use App\Booking;
use Validator;
use App\Http\Requests;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**-
     * Review on profile.
     * 
     * @return \Illuminate\Http\Response
     */
    public function insert(Request $request)
    {        
        $email      = $request->email;
        $code       = $request->token;
        $profile_id = $request->profileId;
        $booking_id = $request->bookingId;
        $rating     = $request->rating;
        $review     = $request->review;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $user = Users::where($where)->value('id');
            $userCount = count($user);

            if($userCount > 0)
            {                
                if($rating !="" && $review !=""){                    
                    $where = ['id' => $booking_id, 'user_id' => $user]; 
                    $bookconf_user = Booking::where($where)->count();

                    if($bookconf_user > 0)
                    {
                        $bookconf_date = Booking::where($where)->whereRaw('book_start_time >= now()-INTERVAL 14 DAY')->count();
                        if($bookconf_date >0){
                            $review_where = ['profile_id' => $profile_id, 'booking_id' => $booking_id];
                            $reviewCount =  Review::where($review_where)->where('user_id',$user)->count();
                            if($reviewCount <= 0){
                                $review_data = new Review;
                                $review_data->profile_id    = $profile_id;
                                $review_data->user_id       = $user;
                                $review_data->rating        = $rating;
                                $review_data->review        = $review;
                                $review_data->booking_id    = $booking_id;

                                $res = ($review_data->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Review successfully added") : array('status' => 0, 'error-code'=>100, 'message' => "Sorry! There was an error in saving the Review");
                                return response()->json($res);
                            }else{
                                $res = array('status' =>0,'error-code'=>307, 'message' =>"user has already given review for this business and booking.");
                                return response()->json($res);                            
                            }
                        }else{
                            $res = array('status' => 0,'error-code'=>307, 'message' => "Sorry! submission time has been expired.");     
                            return response()->json($res);
                        }
                    }else{
                        $res = array('status' => 0,'error-code'=>307, 'message' => "Sorry! Booking is not related this user.");     
                        return response()->json($res);           
                    }
                }else{
                    $res = array('status' => 0,'error-code'=>201, 'message' => "Rating/Review are blank");     
                    return response()->json($res);
                } 
            }else{
                $res = array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");     
                return response()->json($res);           
            }

        }else{
            $res =  array('status' => 0,'error-code'=>209, 'message' => "Sorry! Email or token is invalid or blank") ;
            return response()->json($res);
        }
    }


    public function show(Request $request)
    {        
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profile_id;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $user = Users::where($where);
            $userCount = $user->count();            
                                
            if($userCount > 0)
            {                                  
                $where = ['profile_id' => $profile_id]; 
                $reviewdetails =  Review::where($where)->get();

                if($reviewdetails->count() > 0){
                    $attributes = $reviewdetails->toArray();

                    $attributes['status']= 1;
                    $attributes['error-code']= 0;      
                    return response()->json($attributes);
                }else{
                    $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry! No Review found");     
                    return response()->json($res);
                }


            }else{
                $res = array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");     
                return response()->json($res);           
            }            

        }else{
            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! Email or token is invalid or blank") ;
            return response()->json($res);
        }
    }


}
