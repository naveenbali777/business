<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Users;
use App\Message;
use App\BusinessUser;
use App\Profile;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $name       = $request->name;
        $email      = $request->email;
        $phone      = $request->phone;
        $password   = $request->password;
        $code       = str_random(64);

        if($name !="" && $email !="" && $phone !="" && $password !="")
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

            $users = Users::where('phone', $phone);
            if($users->count() > 0) {
                $res = array('status' => 0,'error-code'=>102, 'message' => "user for this Mobile no. already exists");
                return response()->json($res);
            }

            $user = new Users;
            $user->name             = $name;
            $user->email            = $email;
            $user->phone            = $phone;
            $user->remember_token   = $code;
            $user->password         = md5($password);

            $res = ($user->save()) ? array('status' => 1,'error-code'=>0, 'message' => "user successfully added") : array('status' => 0, 'error-code'=>100, 'message' => "Sorry! There was an error in saving the user");

            if($res['status'] == 1){
                $send = $this->email_sent($email,$code,$name);
            }

            return response()->json($res);
        }else if($name ==""){
            $res =  array('status' => 0,'error-code'=>103, 'message' => "Display Name is empty") ;
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
            $userCount = Users::where($whereThese)->count();

            $code = str_random(64);
                    
            if($userCount > 0) {
                $where = ['email' => $email];
                $user = Users::where($where)->update(array('remember_token' => $code)); 
                $user_id = Users::where($where)->value('id');                       
            }

            $res = ($userCount > 0) ? array('status' => 1,'error-code'=>0, 'message' => "User Found",'token' => $code,'userId' => $user_id) : array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");
            return response()->json($res);

        }else if($email ==""){
            $res =  array('status' => 0,'error-code'=>202, 'message' => "Email is empty") ;
            return response()->json($res);

        }else if($password ==""){
            $res =  array('status' => 0,'error-code'=>203, 'message' => "Password is empty") ;
            return response()->json($res);
        }
    }

    public function details(Request $request)
    {
        $code = $request->code;

        if($code !="")
        {
            $whereThese = ['remember_token' => $code];
            $user = Users::where($whereThese);
            $userCount = $user->count();

            $res = ($userCount > 0) ? array('status' => 1,'error-code'=>0, 'message' => "User Found",'user_deatils' => $user->get()) : array('status' => 0,'error-code'=>201, 'message' => "Sorry! User not found");
            return response()->json($res);

        }else{
            $res =  array('status' => 0,'error-code'=>202, 'message' => "token code is empty") ;
            return response()->json($res);
        }
    }

    public function message_save(Request $request)
    {
        $user_id    = $request->userId;

        if(isset($user_id) && $user_id > 0)
        {
            $user_detail = Users::find($userId);
            if(count($user_detail) > 0){
                $name       = $user_detail->name;
                $email      = $user_detail->email;
                $phone      = $user_detail->phone;
            }else{
                $res = array('status' => 0,'error-code'=>301, 'message' => "Sorry! User not found");
                return response()->json($res);
            }

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

    public function email_sent()
    {
        $code = str_random(32);
        $email = 'naveenbali811@gmail.com';

        $sent = Mail::send('email_send', ['email' => $email,'code' => $code, 'name' => 'naveen'], function($message) use ($email) {
            $message->to($email)->subject('Verify your email address');
        });

        return $sent;
    }
    
}
