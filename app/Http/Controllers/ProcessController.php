<?php

namespace App\Http\Controllers;

use Mail;
use App\BusinessUser;
use App\Users;
use App\MobileVerification;
use Validator;
use App\Http\Requests;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    /**
     * Process of new user.
     * 
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $email      = $request->email;
        $password   = $request->password;

        if($email !="" && $password !="")
        {
            $whereThese = ['email' => $email, 'password' => md5($password)];
            $user = BusinessUser::where($whereThese);
            $userCount = $user->count();
            
            $code = str_random(64);
                    
            if($userCount > 0) {
                $where = ['email' => $email];
                $user = BusinessUser::where($where)->update(array('remember_token' => $code));                        
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

    public function register(Request $request)
    {
        $name       = $request->displayName;
        $category   = $request->category;
        $email      = $request->email;
        $phone      = $request->phone;
        $password   = $request->password;
        $code       = str_random(32);

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

            $users = Users::where('email', $email);
            $busers = BusinessUser::where('email', $email);
            if($users->count() > 0 || $busers->count() > 0) {
                $res = array('status' => 0,'error-code'=>101, 'message' => "user for this email already exists");
                return response()->json($res);
            }

            $users = BusinessUser::where('phone', $phone);
            if($users->count() > 0) {
                $res = array('status' => 0,'error-code'=>102, 'message' => "user for this Mobile no. already exists");
                return response()->json($res);
            }

            $user = new BusinessUser;
            $user->name             = $name;
            $user->category         = $category;
            $user->email            = $email;
            $user->phone            = $phone;
            $user->remember_token   = $code;
            $user->password         = md5($password);

            $res = ($user->save()) ? array('status' => 1,'error-code'=>0, 'message' => "user successfully added",'token' => $code) : array('status' => 0, 'error-code'=>100, 'message' => "Sorry! There was an error in saving the user");

            if($res['status'] == 1){
                $send = $this->email_sent($email,$code,$name);
            }

            return response()->json($res);
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

    public function logout(Request $request)
    {

        $where = ['email' => $request->email];
        $user = BusinessUser::where($where);
        $userCount = $user->count();        
                
        if($userCount > 0) {
            $user = BusinessUser::where($where)->update(array('remember_token' => NULL)); 
            $res =  array('status' => 1,'error-code'=>200, 'message' => "user successfully logout") ;
            return response()->json($res);                       
        
        } else {
            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");
            return response()->json($res);
        }
        
    }

    public function checkUser(Request $request)
    {
        $email  = $request->email;
        $code   = $request->token;

        if($email != "" && $code != "")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $user = BusinessUser::where($where);
            $userCount = $user->count();

            $res = ($userCount > 0) ? array('status' => 1,'error-code'=>0, 'message' => "User Found") : array('status' => 0,'error-code'=>301, 'message' => "Sorry! User not login");
            return response()->json($res);

        }else{

            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");
            return response()->json($res);
        }
    }

    public function verifyPhoneAccount(Request $request)
    { 
        $email  = $request->email;
        $code   = $request->token;

        if($email != "" && $code != "")
        {
            $where = ['email' => $email, 'remember_token' => $code];
            $buserCount = BusinessUser::where($where)->count();            
            $userCount = Users::where($where)->count();

            if($buserCount > 0){
                $bPhoneVerication = BusinessUser::where($where)->where('phone_verify_status',1)->count();
                if($bPhoneVerication > 0){
                    $res = array('status' => 1,'error-code'=>0, 'message' => "Phone has been verified");
                    return response()->json($res);
                }else{
                    $res = array('status' => 0,'error-code'=>301, 'message' => "Sorry! User has not verifed phone");
                    return response()->json($res);
                }
            }elseif($userCount > 0) {
                $PhoneVerication = Users::where($where)->where('phone_verify_status',1)->count();
                if($PhoneVerication > 0){
                    $res = array('status' => 1,'error-code'=>0, 'message' => "Phone has been verified");
                    return response()->json($res);
                }else{
                    $res = array('status' => 0,'error-code'=>301, 'message' => "Sorry! User has not verifed phone");
                    return response()->json($res);
                }            
            }else{
                $res = array('status' => 0,'error-code'=>301, 'message' => "Sorry! User not found");
                return response()->json($res);
            }
        }else{
            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! Email or token is invalid or blank");
            return response()->json($res);
        }
    }


    public function email_verification(Request $request)
    {
        $email = $request->e;
        $code = $request->c;
       
        if($code !="" && $email !="" && strlen($code) == 32)
        {          
            $where = ['email' => $email, 'remember_token' => $code];
            $buserCount = BusinessUser::where($where)->count();           
            $userCount = Users::where($where)->count();

            if($buserCount > 0){
                $update = BusinessUser::where($where)->update(['email_verify_status' =>1]);
                $res = array('status' => 1,'error-code'=>0, 'message' => "Email has been verified.") ;
                return response()->json($res);

            }elseif($userCount > 0) {
                $update =  Users::where($where)->update(['email_verify_status' =>1]);
                $res = array('status' => 1,'error-code'=>0, 'message' => "Email has been verified.") ;
                return response()->json($res);   

            }else{
                $res = array('status' => 0,'error-code'=>301, 'message' => "Sorry! User not found");
                return response()->json($res);
            }
        }else{
            $res =  array('status' => 0,'error-code'=>201, 'message' => "Sorry! link is invalid or broken");
            return response()->json($res);
        }
    }

   public function email_sent()
    {
        $code = str_random(32);
        $email = 'naveenbali811@gmail.com';

        $sent = Mail::send('email_send', ['email' => $email,'code' => $code, 'name' => 'naveen'], function($message) use ($email) {
            $message->to($email)->subject('Verify your email address');
        });

        return $sent;
    }


    public function message_save(Request $request)
    {
        $email  = $request->email;
        $code   = $request->token;

        if($email !="" && $code !="")
        {
            $where = ['email' => $email, 'remember_token' => $code ];
            $user_detail = Users::select('name','phone','id')->where($where)->first();

            echo count($user_detail);

            die('check');
            
            $name       = $user_detail->name;
            $phone      = $user_detail->phone;
            $user_id    = $user_detail->id;

        }else{

            $name       = $request->name;
            $email      = $request->email;
            $phone      = $request->phone; 
            $user_id    = 0;          

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

        $msg_text = $request->message;
        $profile_id = $request->profileId;

        if($msg_text !="" && $email !="")
        {           
            $profileCount = Profile::where('id',$profile_id)->count();
            if($profileCount > 0){
                $msg = new Message;
                $msg->name       = $name;
                $msg->user_email = $email;
                $msg->phone      = $phone;
                $msg->user_id    = $user_id;
                $msg->message    = $msg_text;
                $msg->business_profile_id    = $profile_id;

                $res = ($msg->save()) ? array('status' => 1,'error-code'=>0, 'message' => "Message successfully saved") : array('status' => 0, 'error-code'=>800, 'message' => "Sorry! There was an error in saving the message");
                return response()->json($res);
             }else{
                $res =  array('status' => 0,'error-code'=>803, 'message' => "ProfileId does not exists.") ;
                return response()->json($res);
            }
        }else if($msg_text ==""){
            $res =  array('status' => 0,'error-code'=>803, 'message' => "Message is empty") ;
            return response()->json($res);
        }else if($email ==""){
            $res =  array('status' => 0,'error-code'=>105, 'message' => "Email is empty") ;
            return response()->json($res);
        }
    }


}
