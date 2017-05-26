<?php

namespace App\Http\Controllers;

use App\BusinessUser;
use App\TouringAddress;
use App\BusinessAddress;
use App\Profile;
use App\ExtraProfile;
use App\Review;
use App\ReviewRespond;
use App\Booking;
use App\Message;
use Validator;
use App\Http\Requests;
use Illuminate\Http\Request;

class BusinessBackend extends Controller
{
    /**-
     * Review on profile.
     * 
     * @return \Illuminate\Http\Response
     */   

    public function reviews(Request $request)
    { 
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profile_id;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)
                {
                    $where = ['profile_review.profile_id' => $profile_id]; 
                    $reviewdetails = Review::select('profile_review.*','booking.name','booking.book_start_time','booking.in_out_call','booking.outcall_address')->where($where)->orderBy('profile_review.created_at', 'desc')->join('booking', 'booking.id', '=', 'profile_review.booking_id')->paginate(10);

                    if($reviewdetails->count() > 0)
                    {
                        $attributes_data = $reviewdetails->toArray();
                        $attributes = $attributes_data['data'];
                        $reviews = array();

                        for($i=0;$i<count($attributes);$i++){
                            $rev_id  = $attributes[$i]['review_id'];
                           
                            $reviews[$i]['reviewId']        = $rev_id;
                            $reviews[$i]['rating']          = $attributes[$i]['rating'];
                            $reviews[$i]['review']          = $attributes[$i]['review'];
                            $reviews[$i]['reviewDateTime']  = $attributes[$i]['created_at'];
                            $reviews[$i]['name']            = $attributes[$i]['name'];
                            $reviews[$i]['bookingDateTime'] = $attributes[$i]['book_start_time'];
                            $reviews[$i]['inOutCall']       = $attributes[$i]['in_out_call'];

                            if($attributes[$i]['in_out_call'] == 'Outcall'){
                                $reviews[$i]['outcallAddress']  = $attributes[$i]['outcall_address'];
                            }
                            $revdate = date_create(date('Y-m-d', strtotime($attributes[$i]['created_at'])));
                            $current_date = date_create(date('Y-m-d'));
                            $diff12 = date_diff($revdate ,$current_date);
                            $days = $diff12->days;

                            $respond =  ReviewRespond::where('review_id',$rev_id)->first();
                            if(count($respond) > 0){
                                $respond_data = $respond->toArray();
                                $reviews[$i]['responded']    = 1;
                                $reviews[$i]['responceId']   = $respond_data['id'];
                                $reviews[$i]['responceText'] = $respond_data['reply_text'];
                                if($days <= 14 ){                            
                                    $reviews[$i]['responceCanEdit']  = 1;
                                }else{
                                    $reviews[$i]['responceCanEdit']  = 0;
                                }
                            }else{
                                $reviews[$i]['responded']    = 0;
                                if($days <= 14 ){                            
                                    $reviews[$i]['responceCanAdd']  = 1;
                                }else{
                                    $reviews[$i]['responceCanAdd']  = 0;
                                }
                            }
                        }

                        $allreviews['status']      = 1;
                        $allreviews['error-code']  = 0;
                        $allreviews['total']       = $attributes_data['total'];
                        $allreviews['perPage']     = $attributes_data['per_page'];
                        $allreviews['currentPage'] = $attributes_data['current_page'];
                        $allreviews['lastPage']    = $attributes_data['last_page'];
                        $allreviews['nextPageUrl'] = $attributes_data['next_page_url'];
                        $allreviews['prevPageUrl'] = $attributes_data['prev_page_url'];
                        $allreviews['from']        = $attributes_data['from'];
                        $allreviews['to']          = $attributes_data['to'];                        
                        $allreviews['list']        = $reviews;
                        return response()->json($allreviews);
                    }else{
                        $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry! No Review found");     
                        return response()->json($res);
                    }

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
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

    public function save_review_reply(Request $request)
    { 
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;
        $review_id = $request->reviewId;
        $respond_id = $request->responceId;
        $respond_text = $request->responceText;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();
            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();
                if($profileCount > 0)
                {                   
                    $reviewCount =  Review::where('review_id',$review_id)->count();
                    if($reviewCount > 0)
                    {
                        $revconf_date = Review::where('review_id',$review_id)
                                        ->whereRaw('created_at >= now()-INTERVAL 14 DAY')->count(); 
                        if($revconf_date > 0){
                            $respond_where = ['review_id' => $review_id,'id' => $respond_id];
                            $respondCount =  ReviewRespond::where($respond_where)->count();

                            if($respondCount > 0 && !empty($respond_text) && $respond_id > 0)
                            {             
                                ReviewRespond::where('id',$respond_id)->update(['reply_text' => $respond_text]);
                                $res = array('status' => 1,'error-code'=> 0,'message' => "Responce successfully updated");
                                return response()->json($res);
                            }elseif(!empty($respond_text)){
                                $reply_data = new ReviewRespond;
                                $reply_data->profile_id    = $profile_id;
                                $reply_data->review_id     = $review_id;                            
                                $reply_data->reply_text    = $respond_text;

                                $res = ($reply_data->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Responce successfully added") : array('status' => 0, 'error-code'=>100, 'message' => "Sorry! There was an error in saving the Responce");
                                return response()->json($res);
                            }else{
                                $res = array('status' => 0,'error-code'=>909,'message' => "Sorry! ResponceText is blank") ;
                                return response()->json($res);
                            }
                        }else{
                            $res = array('status' => 0,'error-code'=>307, 'message' => "Sorry! submission time has been expired.");     
                            return response()->json($res);
                        }
                    }else{
                        $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry! No Review found");     
                        return response()->json($res);
                    }

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
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

    public function bookdetails(Request $request)
    { 
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)
                {

                    $where = ['business_profile_id' => $profile_id];
                    $bookingdetails =  Booking::where($where)->orderBy('created_at', 'desc')->paginate(10);

                    if($bookingdetails->count() > 0){
                        $attributes_data = $bookingdetails->toArray();
                        $attributes = $attributes_data['data'];
                        $bookdetails = array();
                        for($i=0;$i<count($attributes);$i++){
                            $bookdetails[$i]['bookingId']       = $attributes[$i]['id'];
                            $bookdetails[$i]['name']            = $attributes[$i]['name'];
                            $bookdetails[$i]['phone']           = $attributes[$i]['phone'];
                            $bookdetails[$i]['email']           = $attributes[$i]['user_email'];
                            $bookdetails[$i]['bookDateTime']    = $attributes[$i]['book_start_time'];
                            $bookdetails[$i]['duration']        = $attributes[$i]['uses_time'];
                            $bookdetails[$i]['confirmationWay'] = $attributes[$i]['confirmation_way'];
                            $bookdetails[$i]['inOutCall']       = $attributes[$i]['in_out_call'];                        
                            $bookdetails[$i]['additionalInfo']  = $attributes[$i]['additional_info'];
                            $bookdetails[$i]['confirmation']    = $attributes[$i]['confirmation'];

                            if($attributes[$i]['in_out_call'] == 'Outcall'){
                                $bookdetails[$i]['hotelName']      = $attributes[$i]['hotel_name'];
                                $bookdetails[$i]['roomNo']         = $attributes[$i]['room_no'];
                                $bookdetails[$i]['outcallAddress'] = $attributes[$i]['outcall_address'];
                            }

                        }

                        $booking['status']      = 1;
                        $booking['error-code']  = 0;
                        $booking['total']       = $attributes_data['total'];
                        $booking['perPage']     = $attributes_data['per_page'];
                        $booking['currentPage'] = $attributes_data['current_page'];
                        $booking['lastPage']    = $attributes_data['last_page'];
                        $booking['nextPageUrl'] = $attributes_data['next_page_url'];
                        $booking['prevPageUrl'] = $attributes_data['prev_page_url'];
                        $booking['from']        = $attributes_data['from'];
                        $booking['to']          = $attributes_data['to'];
                        $booking['list']        = $bookdetails;

                        return response()->json($booking);
                        $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry! No Booking found");     
                        return response()->json($res);
                    }

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
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


    public function bookconfirm (Request $request)
    { 
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;
        $booking_id = $request->bookingId;
        $action_value = $request->actionValue;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)
                {
                    if($action_value == 'cancel'){
                        $curtime = time();
                        $bwhere = ['business_profile_id' => $profile_id, 'confirmation' => 'confirm'];     
                        $check =  Booking::where($bwhere)->where('id',$booking_id)->where('book_start_time','>',$curtime);
                    }else{
                        $bwhere = ['business_profile_id' => $profile_id, 'confirmation' => NULL];     
                        $check =  Booking::where($bwhere)->where('id',$booking_id); 
                    }

                    if($check->count()){
                        $confirmbook =  booking::where('id',$booking_id)->update(['confirmation' => $action_value]);

                        $overlap_time = Booking::where('id',$booking_id)->value('book_start_time');
                        $disable_booking =  Booking::where($bwhere)->where('book_start_time', $overlap_time )
                                            ->update(['confirmation' => 'disable']);
                       
                       $msg_value = ($action_value == 'confirm') ? 'confirmed' : (($action_value == 'reject') ? 'rejected' : (($action_value == 'remove') ? 'removed' : (($action_value == 'cancel') ? 'canceled' : '')));

                        $res = array('status' => 1,'error-code'=>0, 'message' => "Booking has been ".$msg_value);    
                        return response()->json($res);

                    }elseif($action_value == 'cancel'){
                        $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry, you are too late. Period of cancelation is expired");    
                        return response()->json($res);
                    }

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
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


    public function bookingcal(Request $request)
    { 
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)
                {
                    $curtime = time();
                    $where = ['business_profile_id' => $profile_id, 'confirmation' => 'confirm'];
                    $bookingdetails =  Booking::where($where)->orderBy('book_start_time', 'desc')->get();

                    if($bookingdetails->count() > 0){
                        $attributes = $bookingdetails->toArray();
                        $bookdetails = array();
                        for($i=0;$i<count($attributes);$i++){
                            $bookdetails[$i]['bookingId']       = $attributes[$i]['id'];
                            $bookdetails[$i]['name']            = $attributes[$i]['name'];
                            $bookdetails[$i]['phone']           = $attributes[$i]['phone'];
                            $bookdetails[$i]['email']           = $attributes[$i]['user_email'];
                            $bookdetails[$i]['bookDateTime']    = $attributes[$i]['book_start_time'];
                            $bookdetails[$i]['duration']        = $attributes[$i]['uses_time'];
                            $bookdetails[$i]['confirmationWay'] = $attributes[$i]['confirmation_way'];
                            $bookdetails[$i]['inOutCall']       = $attributes[$i]['in_out_call'];                        
                            $bookdetails[$i]['additionalInfo']  = $attributes[$i]['additional_info'];
                            $bookdetails[$i]['bookingWay']      = $attributes[$i]['booking_way'];

                            if($attributes[$i]['in_out_call'] == 'Outcall'){
                                $bookdetails[$i]['hotelName']      = $attributes[$i]['hotel_name'];
                                $bookdetails[$i]['roomNo']         = $attributes[$i]['room_no'];
                                $bookdetails[$i]['outcallAddress'] = $attributes[$i]['outcall_address'];
                            }

                            if($attributes[$i]['book_start_time'] >  $curtime){
                                $bookdetails[$i]['cancel']  = 1 ;
                            }else{
                                $bookdetails[$i]['cancel']  = 0 ;
                            }

                        }
                        $booking['status']= 1;
                        $booking['error-code']= 0;
                        $booking['list']= $bookdetails;

                    }else{
                        $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry! No Booking found");     
                        return response()->json($res);
                    }

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
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

    public function manual_booking(Request $request)
    {
        $b_email    = $request->email;
        $code       = $request->token;
        $profile_id = $request->profileId;

        if($b_email !="" && $code !="")
        {
            $where = ['email' => $b_email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)
                {
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

                    if($name !="" && $email !="" && $phone !="" )
                    {         
                        $bst = date("Y-m-d H:i:s",strtotime(str_replace("/", "-",$request->bookingDate)." ".$request->bookingTime));
                        $book_count = 0 ;
                        $duration = $request->duration;
                        
                        if($duration != 'Weekend' && $duration != 'Dinner date'){                    
                            $bet  = date("Y-m-d H:i:s",(strtotime("+ ".$duration, strtotime($bst))));

                            $book_count =  Booking::where('business_profile_id', $profile_id)
                            ->whereRaw("(book_start_time <= '".$bst."' AND book_end_time >= '".$bst."'")
                            ->orWhereRaw("book_start_time <= '".$bet."' AND book_end_time >= '".$bet."'")
                            ->orWhereRaw("book_start_time >= '".$bst."' AND book_end_time <= '".$bet."')")
                            ->count();
                        }

                      
                        if($book_count <= 0){
                            
                            $duration = $request->duration;                                        

                            $book = new Booking;
                            $book->name                 = $name;
                            $book->user_email           = $email;
                            $book->phone                = $phone;
                            $book->business_profile_id  = $profile_id;
                            $book->confirmation_way     = $request->confirmation;           
                            $book->book_start_time      = $bst;
                            $book->uses_time            = $duration;
                            $book->in_out_call          = $request->bookingIncallOutcall;
                            $book->hotel_name           = $request->hotelName;
                            $book->room_no              = $request->roomNo;
                            $book->outcall_address      = $request->outcallAddress;
                            $book->additional_info      = $request->bookingAdditional;
                            $book->confirmation         = 'confirm';
                            $book->booking_way          = 'manualBooking';
                            
                            if($duration != 'Weekend' && $duration != 'Dinner date'){                    
                                $book->book_end_time  = $bet;
                            }

                            $res = ($book->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Successfully booked") : array('status' => 0, 'error-code'=>100, 'message' => "Sorry! There was an error in booking");                            

                            $bwhere = ['business_profile_id' => $profile_id, 'confirmation' => NULL, 'book_start_time' => $bst]; 
                            $disable_booking = Booking::where($bwhere)->update(['confirmation' => 'disable']);

                            return response()->json($res);

                        }else{
                            $res =  array('status' => 0,'error-code'=>905, 'message' => "Booking is already for this time-frame.") ;
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

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
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


    public function messages(Request $request)
    { 
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)
                {
                    $where = ['business_profile_id' => $profile_id];
                    $messagedetails =  Message::select('id','user_email','name','phone','message','status','created_at')
                    ->where($where)->orderBy('user_email', 'desc')->orderBy('created_at', 'desc')->paginate(10);
                    
                    if($messagedetails->count() > 0){
                        $attributes_data = $messagedetails->toArray();
                        $attributes = $attributes_data['data'];

                        $messages_all = array();
                        $messages = array();
                        $useremail = "";

                        for($i=0;$i<count($attributes);$i++)
                        {
                            if($useremail != "" && $useremail == $attributes[$i]['user_email'])
                            {
                                $k++;
                                $read_status = ($attributes[$i]['status'] == 1) ? 'read' : 'unread';

                                $messages['data'][$k]['messageId'] = $attributes[$i]['id'];
                                $messages['data'][$k]['status']  = $read_status;
                                $messages['data'][$k]['message'] = $attributes[$i]['message'];
                                $messages['data'][$k]['messageDateTime'] = $attributes[$i]['created_at'];
                                if($attributes[$i]['name'] != "" && $attributes[$i]['name'] != NULL){
                                    $messages['data'][$k]['name']   = $attributes[$i]['name'];
                                }
                                if($attributes[$i]['phone'] != "" && $attributes[$i]['phone'] != NULL){
                                    $messages['data'][$k]['phone']   = $attributes[$i]['phone'];
                                }
                            }
                            else{
                                if(!empty($messages)){
                                    $messages_all[] = $messages;
                                    $messages = array();                                  
                                }
                                $k=0;
                                $messages['email'] = $attributes[$i]['user_email'];

                                $read_status = ($attributes[$i]['status'] == 1) ? 'read' : 'unread';

                                $messages['data'][$k]['messageId'] = $attributes[$i]['id'];
                                $messages['data'][$k]['status']  = $read_status;
                                $messages['data'][$k]['message'] = $attributes[$i]['message'];
                                $messages['data'][$k]['messageDateTime'] = $attributes[$i]['created_at'];
                                if($attributes[$i]['name'] != "" && $attributes[$i]['name'] != NULL){
                                    $messages['data'][$k]['name']   = $attributes[$i]['name'];
                                }
                                if($attributes[$i]['phone'] != "" && $attributes[$i]['phone'] != NULL){
                                    $messages['data'][$k]['phone']   = $attributes[$i]['phone'];
                                }
                            }
                            $useremail = $attributes[$i]['user_email'];
                            
                            if($i == count($attributes) -1){
                                if(!empty($messages)){
                                    $messages_all[] = $messages;
                                    $messages = array();                                  
                                }
                            }                   
                        }
                        $allMessages['status']      = 1;
                        $allMessages['error-code']  = 0;
                        $allMessages['total']       = $attributes_data['total'];
                        $allMessages['perPage']     = $attributes_data['per_page'];
                        $allMessages['currentPage'] = $attributes_data['current_page'];
                        $allMessages['lastPage']    = $attributes_data['last_page'];
                        $allMessages['nextPageUrl'] = $attributes_data['next_page_url'];
                        $allMessages['prevPageUrl'] = $attributes_data['prev_page_url'];
                        $allMessages['from']        = $attributes_data['from'];
                        $allMessages['to']          = $attributes_data['to'];
                        $allMessages['list']        = $messages_all;
                        return response()->json($allMessages);
                    }else{
                        $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry! No message found");     
                        return response()->json($res);
                    }

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
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

    public function message_read(Request $request)
    {
        $email      = $request->email;
        $code       = $request->token;
        $message_id = $request->messageId;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {  
                $messageCount = Message::where('id', $message_id)->count();
                if($messageCount > 0)
                {
                    $msg_update =  Message::where('id',$message_id)->update(['status' => 1]);                
                    $res = array('status' => 1,'error-code'=>0, 'message' => "Message status 'read' successfully updated");           
                    return response()->json($res);                    

                }else{
                    $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry! No message found");    
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


    function touraddress(Request $request)
    {
        $email  = $request->email;
        $code   = $request->token;
        $profile_id   = $request->profileId;
        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)
                {
                    if(strtotime($request->finishDate) > strtotime($request->activeDate))
                    {
                        $start_date = date("Y-m-d H:i:s",strtotime($request->activeDate));
                        $end_date = date("Y-m-d H:i:s",strtotime($request->finishDate));

                        $tour_address =  TouringAddress::where('profile_id', $profile_id)
                        ->whereRaw("(touring_sdate <= '".$start_date."' AND touring_edate >= '".$start_date."'")
                        ->orWhereRaw("touring_sdate <= '".$end_date."' AND touring_edate >= '".$end_date."')")
                        ->orWhereRaw("touring_sdate <= '".$start_date."' AND touring_edate >= '".$end_date."')")
                        ->count();

                        echo "<pre>";
                        print_r($tour_address);

                        if($tour_address <= 0){

                            $tour_address = new TouringAddress;
                            $tour_address->address             = $request->address;
                            $tour_address->profile_id          = $profile_id;
                            $tour_address->inoutcall           = $request->inOutCall;
                            $tour_address->outcall_distance    = $request->outCallDistance;
                            $tour_address->tour_lat            = $request->lat;
                            $tour_address->tour_lang           = $request->lng;
                            $tour_address->touring_sdate       = $start_date;
                            $tour_address->touring_edate       = $end_date;
                            $tour_address->hide                = $request->hide;  

                            $res = ($tour_address->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Address successfully updated") : array('status' => 0, 'error-code'=>302, 'message' => "Sorry! There was an error in updating the Address");
                            return response()->json($res);
                        }else{
                            $res = array('status' => 0,'error-code'=>701, 'message' => "Dates are clashing from other saved tours.");  
                            return response()->json($res);         
                        }

                    }else{
                        $res = array('status' => 0,'error-code'=>701, 'message' => "Start date is greater then end date.");  
                        return response()->json($res);         
                    }

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
                    return response()->json($res);           
                } 

            }else{
                $res = array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");     
                return response()->json($res);           
            }            

        }else{
            $res =  array('status' => 0,'error-code'=>209, 'message' => "Sorry! Email or token is invalid blank") ;
            return response()->json($res);
        }
    }

    public function tourAddressList(Request $request)
    {
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)
                {
                    $where = ['profile_id' => $profile_id];
                    $addressdetails =  TouringAddress::where($where)->orderBy('created_at', 'desc')->get();

                    if($addressdetails->count() > 0){
                        $attributes = $addressdetails->toArray();

                        $taddress = array();
                        for($i=0;$i<count($attributes);$i++){
                            $taddress[$i]['tourId']             = $attributes[$i]['id'];
                            $taddress[$i]['address']            = $attributes[$i]['address'];                       
                            $taddress[$i]['inOutCall']          = $attributes[$i]['inoutcall'];
                            $taddress[$i]['outCallDistance']     = $attributes[$i]['outcall_distance'];
                            $taddress[$i]['touringStartDate']   = $attributes[$i]['touring_sdate'];                       
                            $taddress[$i]['touringEndDate']     = $attributes[$i]['touring_edate']; 

                        }
                        $tourAddress['status']= 1;
                        $tourAddress['error-code']= 0;
                        $tourAddress['list']= $taddress;
                        return response()->json($tourAddress);
                    }else{
                        $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry! No Tour Address found");    
                        return response()->json($res);
                    }

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");    
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

    public function deleteTourAddress(Request $request)
    {
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;
        $tourid = $request->tourAddressId;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)
                {
                    $where = ['profile_id' => $profile_id, 'id' => $tourid];
                    $addressdetails =  TouringAddress::where($where)->count();

                    if($addressdetails > 0){
                        $addressdetails =  TouringAddress::where($where)->delete();
                        return response()->json($addressdetails);
                    }else{
                        $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry! No Tour Address found");    
                        return response()->json($res);
                    }

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");    
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

    public function dashboard_data(Request $request)
    {
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)   
                {       
                    //Profile Current Location
                    $where = ['profile_id' => $profile_id];
                    $user_address =  BusinessAddress::where($where)->orderBy('created_at', 'desc')->value('address');
                    if(!empty($user_address)){       
                        $attributes['currentlocation']= $user_address;   
                    }        
                    
                    $tour_address =  TouringAddress::where($where)
                                        ->whereRaw('touring_sdate <= now() AND touring_edate >= now()')->value('address');
                    if(!empty($tour_address)){       
                        $attributes['currentlocation']= $tour_address;          
                    }
                    
                    //Profile Website URL
                    $website = ExtraProfile::where($where)->value('website');            
                    $attributes['website']= $website; 

                    //Profile Visibility
                    $visibility = Profile::where('id',$profile_id)->value('visibility');            
                    $attributes['visibility']= $visibility;

                    //Unrespond Request Booking Count
                    $brwhere = ['business_profile_id' => $profile_id, 'confirmation' => NULL];
                    $req_booking_count = Booking::where($brwhere)->count();     
                    $attributes['requestBookingCount']= $req_booking_count; 

                    //Upcoming Confirm Booking Count
                    $bcwhere = ['business_profile_id' => $profile_id, 'confirmation' => 'confirm'];
                    $up_booking_count = Booking::where($bcwhere)->whereRaw('book_start_time >= now()')->count();
                    $attributes['upcomingBookingCount']= $up_booking_count;

                    //30 Dayes Previous Meassage Count
                    $wh = 'created_at >= now() - INTERVAL 30 DAY'; 
                    $message_count = Message::where('business_profile_id',$profile_id)->whereRaw($wh)->count();
                    $attributes['messageCount']= $message_count; 

                    //30 Dayes Previous Reviews Count
                    $review_count = Review::where('profile_id',$profile_id)->whereRaw($wh)->count();
                    $attributes['reviewCount']= $review_count;  

                    $dashboard_data['status']= 1;
                    $dashboard_data['error-code']= 0;
                    $dashboard_data['data']= $attributes;                            
                    
                    return response()->json($dashboard_data);

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
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

    public function upcoming_tours(Request $request)
    { 
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)
                {
                    $where = ['profile_id' => $profile_id]; 
                    $addressdetails =  TouringAddress::where($where)->whereRaw('touring_sdate >= now()')
                                                        ->orderBy('touring_sdate', 'asc')->limit(3)->get();
                    if($addressdetails->count() > 0){
                        $attributes = $addressdetails->toArray();
                        $taddress = array();
                        for($i=0;$i<count($attributes);$i++){
                            $taddress[$i]['tourId']             = $attributes[$i]['id'];
                            $taddress[$i]['address']            = $attributes[$i]['address'];                    
                            $taddress[$i]['inOutCall']          = $attributes[$i]['inoutcall'];
                            $taddress[$i]['outCallDistance']    = $attributes[$i]['outcall_distance'];
                            $taddress[$i]['touringStartDate']   = $attributes[$i]['touring_sdate'];          
                            $taddress[$i]['touringEndDate']     = $attributes[$i]['touring_edate'];
                        }
                        $tourAddress['status']= 1;
                        $tourAddress['error-code']= 0;
                        $tourAddress['list']= $taddress;
                        
                        return response()->json($tourAddress);
                    }else{
                        $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry! No Tour Address found");     
                        return response()->json($res);
                    }

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
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

    public function visibility(Request $request)
    {
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)   
                {               
                    $visibility = $request->visibility; 
                    Profile::where('id',$profile_id)->update(['visibility' => $visibility]); 
                    $res = array('status' => 1,'error-code'=> 0, 'message' => "Visibility successfully updated");
                    return response()->json($res);        

                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
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

    public function graph_values(Request $request)
    {
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $userCount = BusinessUser::where($where)->count();

            if($userCount > 0)                          
            {                                  
                $profile_where = ['user_email' => $email,'id' => $profile_id];
                $profileCount = Profile::where($profile_where)->count();

                if($profileCount > 0)   
                {  //Past 30 Days 

                    $attributes_month['range']= 'Past 30 Days'; 

                    $whM = 'created_at >= now() - INTERVAL 30 DAY'; 
                    $message_cnt_m = Message::where('business_profile_id',$profile_id)->whereRaw($whM)->count();
                    $attributes_month['data']['messagesReceived']= $message_cnt_m; 

                    $booking_cnt_m = Booking::where('business_profile_id',$profile_id)->whereRaw($whM)->count();
                    $attributes_month['data']['bookingRequests']= $booking_cnt_m;
                   
                    $review_cnt_m = Review::where('profile_id',$profile_id)->whereRaw($whM)->count();
                    $attributes_month['data']['ratingsReceived']= $review_cnt_m;
                    
                    $avg_rating_m = Review::where('profile_id',$profile_id)->whereRaw($whM)->avg('rating');
                    $attributes_month['data']['averageRating']= round($avg_rating_m,1); 

                    $book_values_m = Booking::select('bookingCount','date'))->where('business_profile_id',$profile_id)->whereRaw($whM)
                    ->groupBy('date')->orderBy('date','asc')->get();

                    $m_series_data= $book_values_m->toArray();

                    $m_date_range = $this->date_range('+1 day', 'Y-m-d');

                    for($i=0;$i < count($m_date_range); $i++)
                    {
                        $b_data = 0;
                        for($z = 0;$z < count($m_series_data); $z++)
                        {   
                            if($m_date_range[$i] == $m_series_data[$z]['date'])
                            {
                                $b_data = (int)$m_series_data[$z]['bookingCount'];
                            }                 
                        }
                        $m_booking_data[$i] = $b_data;
                        $m_labels_data[$i] = date("d/m",strtotime($m_date_range[$i]));
                    }
                    $m_booking_series['name'] = 'Booking';
                    $m_booking_series['color'] = '#FF0000';
                    $m_booking_series['data'] = $m_booking_data;

                    $attributes_month['data']['series']= array($m_booking_series);
                    $attributes_month['data']['labels']= $m_labels_data;

                    $attributes_year['range']= 'Past year';

                    $whY = 'created_at >= now() - INTERVAL 1 YEAR';  
                    $message_cnt_y = Message::where('business_profile_id',$profile_id)->whereRaw($whY)->count();
                    $attributes_year['data']['messagesReceived']= $message_cnt_y;

                    $booking_cnt_y = Booking::where('business_profile_id',$profile_id)->whereRaw($whY)->count();
                    $attributes_year['data']['bookingRequests']= $booking_cnt_y; 
                   
                    $review_cnt_y = Review::where('profile_id',$profile_id)->whereRaw($whY)->count();
                    $attributes_year['data']['ratingsReceived']= $review_cnt_y;                    

                    $avg_rating_y = Review::where('profile_id',$profile_id)->whereRaw($whY)->avg('rating');
                    $attributes_year['data']['averageRating']= round($avg_rating_y,1); 
                   
                    $book_values_y = Booking::select('bookingCount','monthName'))->where('business_profile_id',$profile_id)->groupBy('monthName')->orderBy('created_at','asc')->get();
                    $y_series_data= $book_values_y->toArray();
                    $y_month_range = $this->date_range('+1 month', 'F');
                    $k = (count($y_month_range) == 13) ? 1 : 0 ;

                    for($i=$k;$i < count($y_month_range); $i++)
                    {
                        
                        $b_data = 0;
                        for($z = 0;$z < count($y_series_data); $z++)
                        {   
                            if($y_month_range[$i] == $y_series_data[$z]['monthName'])
                            {
                                $b_data = (int)$y_series_data[$z]['bookingCount'];
                            }                 
                        }

                        $m = ($k == 1) ? ($i-1) : $i ;
                        $y_booking_data[$m] = $b_data;
                        $y_labels_data[$m] = $y_month_range[$i];
                    }

                    $y_booking_series['name'] = 'Booking';
                    $y_booking_series['color'] = '#FF0000';
                    $y_booking_series['data'] = $y_booking_data;

                    $attributes_year['data']['series']= array($y_booking_series);
                    $attributes_year['data']['labels']= $y_labels_data;                    
                    $attributes['stats'] = array($attributes_month,$attributes_year);      
                    $attributes['status'] = 1;
                    $attributes['error-code'] = 0;

                    return response()->json($attributes);                                


                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
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

    function date_range($first, $last, $step, $output_format)
    {
        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while( $current <= $last ) {

            $dates[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }

        return $dates;
    }



}
