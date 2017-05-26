<?php

namespace App\Http\Controllers;


use App\BusinessUser;
use App\Category;
use App\services;
use App\Profile;
use App\BusinessAddress;
use App\BusinessHours;
use App\BusinessPrice;
use App\ExtraProfile;
use Validator;
use App\Http\Requests;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**-
     * Profile of new Business User.
     * 
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $email  = $request->email;
        $code   = $request->token;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $user = BusinessUser::where($where);
            $userCount = $user->count();
                                
            if($userCount > 0)
            {
                $where = ['user_email' => $email];
                $userdetails =  Profile::where($where)->get();
                $attributes = $userdetails->toArray();
                $pcount = $userdetails->count();
                $userprofiles = array();
                 for($i=0;$i<count($attributes);$i++)
                 {                    
                    $userProfiles[$i]['profileId'] = $attributes[$i]['id'];
                    $userProfiles[$i]['userId'] = $attributes[$i]['user_id'];
                    $userProfiles[$i]['userEmail'] = $attributes[$i]['user_email'];
                    $userProfiles[$i]['name'] = $attributes[$i]['name'];
                    $userProfiles[$i]['dob'] = $attributes[$i]['dob'];
                    $userProfiles[$i]['bodyType'] = $attributes[$i]['body_type'];
                    $userProfiles[$i]['height'] = $attributes[$i]['height'];
                    $userProfiles[$i]['hairColor'] = $attributes[$i]['hair_color'];
                    $userProfiles[$i]['eyeColor'] = $attributes[$i]['eye_color'];
                    $userProfiles[$i]['ethnicity'] = $attributes[$i]['ethnicity'];
                    $userProfiles[$i]['sexuality'] = $attributes[$i]['sexuality'];
                    $userProfiles[$i]['languagesSpoken'] = $attributes[$i]['languages_spoken'];
                    $userProfiles[$i]['tattoos'] = $attributes[$i]['tattoos'];
                    $userProfiles[$i]['piercings'] = $attributes[$i]['piercings'];
                    $userProfiles[$i]['analPreference'] = $attributes[$i]['anal_preference'];
                    $userProfiles[$i]['inOutCall'] = $attributes[$i]['in_out_call'];
                    $userProfiles[$i]['twitter'] = $attributes[$i]['twitter'];
                    $userProfiles[$i]['facebook'] = $attributes[$i]['facebook'];
                    $userProfiles[$i]['instagram'] = $attributes[$i]['instagram'];
                    $userProfiles[$i]['noticeTime'] = $attributes[$i]['notice_time'];
                    $userProfiles[$i]['pubicHair'] = $attributes[$i]['pubic_hair'];
                    $userProfiles[$i]['bustSize'] = $attributes[$i]['bust_size'];
                    $userProfiles[$i]['penisLength'] = $attributes[$i]['penis_length'];
                    $userProfiles[$i]['penisGirth'] = $attributes[$i]['penis_girth'];
                    $userProfiles[$i]['foreskin'] = $attributes[$i]['foreskin'];

                    $cate = explode(",",$attributes[$i]['category_id']);
                    $c_name = Category::select('id','category_name')->whereIn('id', $cate)->get();
                    $attributes[$i]['category'] = $c_name;

                    $sub_serv = explode(",",$attributes[$i]['subservice']);
                    $serv_name = Services::select('service_id','service_name')->whereIn('service_id', $sub_serv)->get();
                    $attributes[$i]['subServices'] = $serv_name;    

                 }

                $userProfiles['status']= 1;
                $userProfiles['error-code']= 0;
                $userProfiles['count']= $pcount;
                $userProfiles['list']= $attributes;

                return response()->json($userProfiles);

            }else{
                $res = array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");     
                return response()->json($res);           
            }            
        }else{
            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found") ;
            return response()->json($res);
        }
    }   

    public function show_profile(Request $request)
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
                    $where = ['user_email' => $email, 'id'=> $request->profileId];
                    $userdetails =  Profile::where($where)->join('services', 'services.service_id', '=', 'business_profile.main_service')->first();
                    $attributes = $userdetails->toArray();

                    $profileDetails['profileId']       = $attributes['id'];
                    $profileDetails['userEmail']       = $attributes['user_email'];
                    $profileDetails['name']            = $attributes['name'];
                    $profileDetails['dob']             = $attributes['dob'];
                    $profileDetails['bodyType']        = $attributes['body_type'];
                    $profileDetails['height']          = $attributes['height'];
                    $profileDetails['hairColor']       = $attributes['hair_color'];
                    $profileDetails['eyeColor']        = $attributes['eye_color'];
                    $profileDetails['ethnicity']       = $attributes['ethnicity'];
                    $profileDetails['sexuality']       = $attributes['sexuality'];
                    $profileDetails['languagesSpoken'] = $attributes['languages_spoken'];
                    $profileDetails['tattoos']         = $attributes['tattoos'];
                    $profileDetails['piercings']       = $attributes['piercings'];
                    $profileDetails['analPreference']  = $attributes['anal_preference'];
                    $profileDetails['inOutCall']       = $attributes['in_out_call'];
                    $profileDetails['outcalldistance'] = $attributes['outcalldistance'];
                    $profileDetails['twitter']         = $attributes['twitter'];
                    $profileDetails['facebook']        = $attributes['facebook'];
                    $profileDetails['instagram']       = $attributes['instagram'];
                    $profileDetails['noticeTime']      = $attributes['notice_time'];
                    $profileDetails['pubicHair']       = $attributes['pubic_hair'];
                    $profileDetails['bustSize']        = $attributes['bust_size'];
                    $profileDetails['penisLength']     = $attributes['penis_length'];
                    $profileDetails['penisGirth']      = $attributes['penis_girth'];
                    $profileDetails['foreskin']        = $attributes['foreskin'];
                    $profileDetails['latitude']        = $attributes['lat'];
                    $profileDetails['longitude']       = $attributes['lang'];  
                    $profileDetails['licenceNo']       = $attributes['licence_no'];
                    $profileDetails['about']           = $attributes['about'];
                    $profileDetails['coreService']     = $attributes['service_name'];

                    $cate = explode(",",$attributes['category_id']);
                    $c_name = Category::select('id','category_name')->whereIn('id', $cate)->get();
                    $profileDetails['category'] = $c_name;

                    $sub_serv = explode(",",$attributes['subservice']);
                    $serv_name = Services::select('service_id','service_name')->whereIn('service_id', $sub_serv)->get();
                    $profileDetails['subServices'] = $serv_name;
                    $userProfile['status']= 1;
                    $userProfile['error-code']= 0;
                    $userProfile['list']= $profileDetails;
                                  
                    return response()->json($userProfile);
                }else{
                    $res = array('status' => 0,'error-code'=>701, 'message' => "Sorry! profileId is not related to this user");     
                    return response()->json($res);           
                }

            }else{
                $res = array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");     
                return response()->json($res);           
            }            

        }else{
            $res =  array('status' => 0,'error-code'=>209, 'message' => "Sorry! Email or token is invalide or blank") ;
            return response()->json($res);
        }
           
    }


    public function register(Request $request)
    {
        $name       = $request->realName;
        $category   = $request->category[0];
        $email      = $request->email;
        $phone      = $request->phone;
        $code       = str_random(64);
        $password   = $request->password;
        
        if($name !="" && $category !="" && $email !="" && $phone !="" && $password !="")
        {
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

            $users = BusinessUser::where('email', $email);
            if($users->count() > 0) {
                $res = array('status' => 0,'error-code'=>101, 'message' => "user for this email already exists");
                return response()->json($res);
            }

            $users = BusinessUser::where('phone', $phone);
            if($users->count() > 0) {
                $res = array('status' => 0,'error-code'=>102, 'message' => "user for this Mobile no. already exists");
                return response()->json($res);
            }

            $user = new BusinessUser;
            $user->name     = $name;
            $user->category = $category;
            $user->email    = $email;
            $user->phone    = $phone;
            $user->remember_token   = $code;
            $user->password = md5($password);

            $res = ($user->save()) ? array('status' => 1,'error-code'=>0, 'message' => "user successfully added") : array('status' => 0, 'error-code'=>100, 'message' => "Sorry! There was an error in saving the user");
           
            $user_details = array('email' => $email, 'token' => $code);
            
            $this->create($user_details,$request);

        }else if($name ==""){
            $res =  array('status' => 0,'error-code'=>103, 'message' => "Display Name is empty") ;
            return response()->json($res);
        }else if($category ==""){
            $res =  array('status' => 0,'error-code'=>104, 'message' => "Category is empty") ;
            return response()->json($res);
        }else if($email ==""){
            $res =  array('status' => 0,'error-code'=>105, 'message' => "Email is empty") ;
            return response()->json($res);
        }else if($phone ==""){
            $res =  array('status' => 0,'error-code'=>106, 'message' => "Mobile is empty") ;
            return response()->json($res);
        }else if($password ==""){
            $res =  array('status' => 0,'error-code'=>107, 'message' => "Password is empty") ;
            return response()->json($res);
        }
    }


    public function create(Request $request)
    {
        $email  = $request->email;
        $code   = $request->token;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $user = BusinessUser::where($where);
            $user_id = $user->value('id');
            $phone_verify_status = $user->value('phone_verify_status');          
            $userCount = $user->count();

            if($userCount > 0)
            {
                $user_profile = new Profile;

                $user_profile->user_id          = $user_id;
                $user_profile->user_email       = $email;
                $user_profile->name             = $request->realName;
                $user_profile->dob              = date("Y-m-d",strtotime($request->dob));
                $user_profile->body_type        = $request->body;
                $user_profile->height           = $request->height;
                $user_profile->hair_color       = $request->hair_color;
                $user_profile->eye_color        = $request->eye_color;
                $user_profile->ethnicity        = $request->ethnicity;
                $user_profile->sexuality        = $request->sexuality;
                $user_profile->languages_spoken = implode(",",$request->languages);
                $user_profile->tattoos          = $request->tattoos;
                $user_profile->piercings        = $request->piercings; ;              
                $user_profile->anal_preference  = $request->anal;
                $user_profile->in_out_call      = implode(",",$request->in_out_call);
                $user_profile->outcalldistance  = $request->outcalldistance;
                $user_profile->request_tour     = $request->tourRequest;
                $user_profile->twitter          = $request->t_email;
                $user_profile->facebook         = $request->f_email;
                $user_profile->instagram        = $request->i_email;
                $user_profile->about            = $request->about;
                $user_profile->notice_time      = $request->notice;
                $user_profile->bt_book_time     = $request->betwenBookings;
                $user_profile->licence_no       = $request->licence_no;
                $user_profile->bust_size        = $request->cupSize;
                $user_profile->penis_length     = $request->penis_length;
                $user_profile->penis_girth      = $request->penis_girth;
                $user_profile->pubic_hair       = $request->pubicHair;
                $user_profile->foreskin         = $request->circumcised;
                $user_profile->main_service     = $request->coreService;
                $user_profile->subservice       = implode(",",$request->services);
                $user_profile->category_id      = implode(",",$request->category);

                if(isset($request->smoker) && $request->smoker !=""){
                    $user_profile->smoker = $request->smoker;                    
                }

                if($phone_verify_status > 0){
                    $user_profile->visibility = 1;
                }

                $res['profile'] = ($user_profile->save()) ? array('status' => 1,'error-code'=>0, 'message' => "profile successfully added") : array('status' => 0, 'error-code'=>300, 'message' => "Sorry! There was an error in saving the profile");
                
                $user_details = array('email' => $email, 'token' => $code);
                $user_details['profile_id'] = $user_profile->id;
                $res['address'] = $this->address_save($user_details,$request);
                $res['hours'] = $this->business_hours($user_details,$request);
                $res['price'] = $this->business_price($user_details,$request);
                $res['extra'] = $this->extra_options($user_details,$request);
                $res['profile_id'] = $user_profile->id;
                return response()->json($res);

            }else{
                $res = array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");     
                return response()->json($res);           
            }             

        }else{
            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found") ;
            return response()->json($res);
        }
    }


    function address_save($user_details,$all_values)
    {
       $email  = $user_details['email'];
        $code   = $user_details['token'];


        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $user = BusinessUser::where($where);
            $userCount = $user->count();            
                                
            if($userCount > 0)
            {                
                $b_address = new BusinessAddress;
                $b_address->profile_id      = $user_details['profile_id'];
                $b_address->address         = $all_values->address;
                /*$b_address->city          = $all_values->city;
                $b_address->state           = $all_values->state;
                $b_address->zip             = $all_values->zip;
                $b_address->lat             = $all_values->lat;
                $b_address->lang            = $all_values->lng;
                $b_address->touring_date    = date("Y-m-d",strtotime($all_values->active_date));*/
                $b_address->hide            = $all_values->hide;

                $res = ($b_address->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Address successfully added") : array('status' => 0, 'error-code'=>301, 'message' => "Sorry! There was an error in saving the Address");
                return response()->json($res);

            }else{
                $res = array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");     
                return response()->json($res);           
            }            

        }else{
            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found") ;
            return response()->json($res);
        }
    }

    function business_hours($user_details,$all_values)
    {
        $email  = $user_details['email'];
        $code   = $user_details['token'];

        $weekdays = array_keys($all_values['weekday']);

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $user = User::where($where);
            $userCount = $user->count();            
                                
            if($userCount > 0)
            {              
                
                foreach ($weekdays as $wday) 
                {                    
                    $b_hours = new BusinessHours;                                                
                    $b_hours->profile_id = $user_details['profile_id'];
                    $b_hours->day        = $wday;

                    if(isset($all_values['weekday'][$wday]['day']) && $all_values['weekday'][$wday]['day'] == 'all')
                    {
                        $b_hours->on_time   = '00:00';
                        $b_hours->off_time  = '23:59'; 
                        $res = ($b_hours->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Business hours successfully added") : array('status' => 0, 'error-code'=>303, 'message' => "Sorry! There was an error in saving the Business hours");

                    }elseif(isset($all_values['weekday'][$wday]['on_time'])) {
                        $b_hours->on_time   = date("H:i:s",strtotime($all_values['weekday'][$wday]['on_time']));
                        $b_hours->off_time  = date("H:i:s",strtotime($all_values['weekday'][$wday]['off_time']));
                        $res = ($b_hours->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Business hours successfully added") : array('status' => 0, 'error-code'=>303, 'message' => "Sorry! There was an error in saving the Business hours");
                    }                             
                }
                 
                return response()->json($res);

            }else{
                $res = array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");     
                return response()->json($res);           
            }            

        }else{
            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found") ;
            return response()->json($res);
        }
    }


    function business_price($user_details,$all_values)
    {

        $email  = $user_details['email'];
        $code   = $user_details['token'];

        $dayprice = array_keys($all_values['price']);

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $user = BusinessUser::where($where);
            $userCount = $user->count();            
                                
            if($userCount > 0)
            {               
                foreach ($dayprice as $dval) 
                { 
                    $b_price = new BusinessPrice;
                    $b_price->profile_id    = $user_details['profile_id'];
                    $b_price->work_time     = $all_values['price'][$dval]['time'];
                    $b_price->incall_price  = $all_values['price'][$dval]['incall'];
                    $b_price->outcall_price = $all_values['price'][$dval]['outcall']; 

                    $res = ($b_price->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Business price successfully added") : array('status' => 0, 'error-code'=>303, 'message' => "Sorry! There was an error in saving the Business price");  
                }
                return response()->json($res);

            }else{
                $res = array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");     
                return response()->json($res);           
            }            

        }else{
            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found") ;
            return response()->json($res);
        }
    }


    function extra_options($user_details,$all_values)
    {

        $email  = $user_details['email'];
        $code   = $user_details['token'];


        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $user = User::where($where);
            $userCount = $user->count();            
                                
            if($userCount > 0)
            {               
                $ext_opt = new ExtraProfile;
                $ext_opt->profile_id = $user_details['profile_id'];
                $isit = 0;

                if(isset($all_values->spa) && $all_values->spa !=""){
                    $ext_opt->spa = $all_values->spa;
                    $isit = 1;
                }
                if(isset($all_values->shower) && $all_values->shower !=""){
                    $ext_opt->shower = $all_values->shower;
                    $isit = 1;
                } 
                if(isset($all_values->smoker) && $all_values->smoker !=""){
                    $ext_opt->smoker = $all_values->smoker;
                    $isit = 1;
                }
                if(isset($all_values->creditCard) && $all_values->creditCard !=""){
                    $ext_opt->cc_accepted = $all_values->creditCard;
                    $isit = 1;
                }
                if(isset($all_values->atm) && $all_values->atm !=""){
                    $ext_opt->atm_site = $all_values->atm;
                    $isit = 1;
                }
                if(isset($all_values->parking) && $all_values->parking !=""){
                    $ext_opt->parking = $all_values->parking;
                    $isit = 1;
                }
                if(isset($all_values->massageClients) && $all_values->massageClients !=""){
                    $ext_opt->massage_for = $all_values->massageClients;
                    $isit = 1;
                }
                if(isset($all_values->website) && $all_values->website !=""){
                    $ext_opt->website = $all_values->website;
                    $isit = 1;
                }         

                $res = ($isit == 1) ? $ext_opt->save() : array('status' => 0, 'error-code'=>303, 'message' => "Sorry! There was an error in saving the Business price");
                return response()->json($res);                

            }else{
                $res = array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");     
                return response()->json($res);           
            }            

        }else{
            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found") ;
            return response()->json($res);
        }
    }

    public function create_profile()
    {

       return view('profile'); 

    }

    public function profile_elements()
    {
        $email  = $request->email;
        $code   = $request->token;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $user = BusinessUser::where($where);
            $userCount = $user->count();            
                                
            if($userCount > 0)
            {             
                $serv_name = services::all();
                return response()->json($serv_name);
            }else{
                $res = array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");     
                return response()->json($res);           
            }            

        }else{
            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found") ;
            return response()->json($res);
        }
    }     


    


    public function address_create()
    {
       return view('address'); 
    }

    public function address_edit($id)
    {
        $profile_address = BusinessAddress::find($id);

        $res = (!empty($profile_address)) ? $profile_address->toArray() : array('status' => 0, 'error-code'=>501, 'message' => "Sorry! Id not found");
        return response()->json($res);
    }


    function address_update($id,Request $request)
    {
        $email  = $request->email;
        $code   = $request->token;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $user = User::where($where);
            $userCount = $user->count();            
                                
            if($userCount > 0)
            {                
                $b_address = BusinessAddress::find($id);
                $b_address->profile_id    = $request->profile_id;
                $b_address->address         = $request->address;
                $b_address->city            = $request->city;
                $b_address->state           = $request->state;
                $b_address->zip             = $request->zip;
                $b_address->lat             = $request->lat;
                $b_address->lang            = $request->lng;
                $b_address->touring_date    = date("Y-m-d",strtotime($request->active_date));
                $b_address->hide            = $request->hide;

                $res = ($b_address->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Address successfully updated") : array('status' => 0, 'error-code'=>302, 'message' => "Sorry! There was an error in updating the Address");
                return response()->json($res);

                $p_position = Profile::find($request->profile_id);
                $p_position->lat            = $request->lat;
                $p_position->lang           = $request->lng;

                $p_position->save();

            }else{
                $res = array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");     
                return response()->json($res);           
            }            

        }else{
            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found") ;
            return response()->json($res);
        }
    }


    public function availability()
    {
       return view('availability'); 
    }
    

    public function edit_business_hours(Request $request)
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
                    $b_hours = BusinessHours::where('profile_id',$profile_id)->get();
                    
                    if($b_hours->count() > 0){
                        $attributes = $b_hours->toArray();
                        $business_hours = array();
                        for($i=0;$i<count($attributes);$i++){
                            $business_h[$i]['day']      = $attributes[$i]['day'];                        
                            $business_h[$i]['onTime']   = $attributes[$i]['on_time'];
                            $business_h[$i]['offTime']  = $attributes[$i]['off_time'];
                        }
                        $business_hours['status']= 1;
                        $business_hours['error-code']= 0;
                        $business_hours['list']= $business_h;

                        return response()->json($business_hours);

                    }else{                        
                        $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry! No BusinessHours found");     
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
            $res =  array('status' => 0,'error-code'=>209, 'message' => "Sorry! Email or token is invalide or blank") ;
            return response()->json($res);
        }
           
    }

    function update_business_hours(Request $request)
    {

        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;
        $weekdays = array_keys($request->weekday);

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
                    BusinessHours::where('profile_id',$profile_id)->delete();

                    foreach ($weekdays as $wday) 
                    {                    
                        $b_hours = new BusinessHours;                                                
                        $b_hours->profile_id = $profile_id;
                        $b_hours->day        = $wday;

                        if(isset($request['weekday'][$wday]['day']) && $request['weekday'][$wday]['day'] == 'all')
                        {
                            $b_hours->on_time   = '00:00';
                            $b_hours->off_time  = '23:59'; 
                            $res = ($b_hours->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Business hours successfully updated") : array('status' => 0, 'error-code'=>303, 'message' => "Sorry! There was an error in updatinging the Business hours");

                        }elseif(isset($request['weekday'][$wday]['on_time'])) {
                            $b_hours->on_time   = date("H:i:s",strtotime($request['weekday'][$wday]['on_time']));
                            $b_hours->off_time  = date("H:i:s",strtotime($request['weekday'][$wday]['off_time']));
                            $res = ($b_hours->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Business hours successfully updated") : array('status' => 0, 'error-code'=>303, 'message' => "Sorry! There was an error in updatinging the Business hours");
                        }                             
                    }
                
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
            $res =  array('status' => 0,'error-code'=>209, 'message' => "Sorry! Email or token is invalide or blank") ;
            return response()->json($res);
        }
    }
    
    public function edit_business_price(Request $request)
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
                    $b_price = BusinessPrice::where('profile_id',$profile_id)->get();

                    if($b_price->count() > 0){
                        $attributes = $b_price->toArray();
                        $business_price = array();
                        for($i=0;$i<count($attributes);$i++){
                            $business_p[$i]['time']      = $attributes[$i]['work_time'];                   
                            $business_p[$i]['incallPrice']   = $attributes[$i]['incall_price'];
                            $business_p[$i]['outcallPrice']  = $attributes[$i]['outcall_price'];
                        }
                        $business_price['status']= 1;
                        $business_price['error-code']= 0;
                        $business_price['list']= $business_p;

                        return response()->json($business_price);

                    }else{                        
                        $res = array('status' => 0,'error-code'=>901, 'message' => "Sorry! No BusinessPrice found");     
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
            $res =  array('status' => 0,'error-code'=>209, 'message' => "Sorry! Email or token is invalide or blank") ;
            return response()->json($res);
        }
           
    }


    function update_business_price(Request $request)
    {
        $email  = $request->email;
        $code   = $request->token;
        $profile_id = $request->profileId;
        $dayprice = array_keys($request->price);

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
                    BusinessPrice::where('profile_id',$profile_id)->delete();               
                    foreach ($dayprice as $dval) 
                    { 
                        $b_price = new BusinessPrice;
                        $b_price->profile_id    = $profile_id;
                        $b_price->work_time     = $request['price'][$dval]['time'];
                        $b_price->incall_price  = $request['price'][$dval]['incall'];
                        $b_price->outcall_price = $request['price'][$dval]['outcall']; 

                        $res = ($b_price->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Business price successfully updated") : array('status' => 0, 'error-code'=>303, 'message' => "Sorry! There was an error in updatinging the Business price");  
                    }           

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
            $res =  array('status' => 0,'error-code'=>209, 'message' => "Sorry! Email or token is invalide or blank") ;
            return response()->json($res);
        }
    }


    public function update_profile_info(Request $request)
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
                    $user_profile = Profile::find($profile_id);

                    $user_profile->user_id        = $user_id;
                    $user_profile->user_email       = $email;
                    $user_profile->name             = $request->realName;
                    $user_profile->body_type        = $request->body;
                    $user_profile->height           = $request->height;
                    $user_profile->hair_color       = $request->hair_color;
                    $user_profile->eye_color        = $request->eye_color;
                    $user_profile->ethnicity        = $request->ethnicity;
                    $user_profile->sexuality        = $request->sexuality;                    
                    $user_profile->tattoos          = $request->tattoos;
                    $user_profile->piercings        = $request->piercings; ;              
                    $user_profile->anal_preference  = $request->anal;                    
                    $user_profile->outcalldistance  = $request->outcalldistance;
                    $user_profile->request_tour     = $request->tourRequest;
                    $user_profile->twitter          = $request->twitter;
                    $user_profile->facebook         = $request->facebook;
                    $user_profile->instagram        = $request->instagram;
                    $user_profile->about            = $request->about;
                    $user_profile->notice_time      = $request->notice;
                    $user_profile->bt_book_time     = $request->betwenBookings;
                    $user_profile->licence_no       = $request->licence_no;
                    $user_profile->bust_size        = $request->cupSize;
                    $user_profile->penis_length     = $request->penis_length;
                    $user_profile->penis_girth      = $request->penis_girth;
                    $user_profile->pubic_hair       = $request->pubicHair;
                    $user_profile->foreskin         = $request->circumcised;                             

                    //incall-outcall
                    if(isset($request->in_out_call) && count($request->in_out_call)>0){
                        $user_profile->in_out_call = implode(",",$request->in_out_call);
                    }
                    //languages
                    if(isset($request->languages) && count($request->languages)>0){
                        $user_profile->languages_spoken = implode(",",$request->languages);
                    }
                    //main servics
                    if(isset($request->coreService) && $request->coreService = ""){
                        $user_profile->main_service   = $request->coreService;                        
                    } 
                    //servics
                    if(isset($request->services) && count($request->services)>0){
                        $user_profile->subservice   = implode(",",$request->services);                        
                    } 
                    //category
                    if(isset($request->category) && count($request->category)>0){
                        $user_profile->category_id = implode(",",$request->category);                        
                    }               

                    $res = ($user_profile->save()) ? array('status' => 1,'error-code'=>0, 'message' => "profile successfully updated") : array('status' => 0, 'error-code'=>300, 'message' => "Sorry! There was an error in updatinging the profile");
                  
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
            $res =  array('status' => 0,'error-code'=>209, 'message' => "Sorry! Email or token is invalide or blank") ;
            return response()->json($res);
        }
    }



}
