<?php

namespace App\Http\Controllers;

use App\Profile;
use App\BusinessUser;
use App\Users;
use App\MobileVerification;
use App\Http\Requests;
use Illuminate\Http\Request;
use Clickatell\Api\ClickatellRest;

class VerifyPhone extends Controller
{
    /**
     * Registeration of new user.
     * 
     * @return \Illuminate\Http\Response
     */
  
    public function sendOTP(Request $request)
    {       
        $mob = $request->phone;

        if($mob !="")
        {
            $verifycode = mt_rand(100000,999999);            

            $clickatell = new ClickatellRest('oou4QZij7Q9RAMIWAjlOKUeVPlrVQygzxvGbxuEsbV02Qt.xYFC64vcphKvT7BJVfkEICt');
            $response = $clickatell->sendMessage(array($mob), "Aussiebiz: OTP for mobile verification is ".$verifycode);
            $users = Users::where('phone', $mob)->count();
            $busers = BusinessUser::where('phone', $mob)->count();

            if($users > 0) {
                $code_update =  Users::where('phone', $mob)->update(['phone_verify_code' => $verifycode]);
                $res = array('status' => 1,'error-code'=>0, 'message' => "OTP has been sent.") ;
                return response()->json($res);
            }elseif($busers > 0){
                $code_update =  BusinessUser::where('phone', $mob)->update(['phone_verify_code' => $verifycode]);
                $res = array('status' => 1,'error-code'=>0, 'message' => "OTP has been sent.") ;
                return response()->json($res);
            }else{
                $unregistered_users = MobileVerification::where('phone', $mob)->count();
                if($unregistered_users > 0){
                    $code_update =  MobileVerification::where('phone', $mob)->update(['phone_verify_code' => $verifycode,'phone_verify_status' => 0]);
                    $res = array('status' => 1,'error-code'=>0, 'message' => "OTP has been sent.");
                }else{
                    $verifyMobile = new MobileVerification;
                    $verifyMobile->phone       = $mob;
                    $verifyMobile->phone_verify_code = $verifycode;
                    $res = ($verifyMobile->save()) ? array('status' => 1,'error-code'=>0, 'message' => "OTP has been sent.") : array('status' => 0, 'error-code'=>100, 'message' => "Sorry! There was an error in sending OTP");
                }              
           
                return response()->json($res);
            }
        }else{
            $res =  array('status' => 0,'error-code'=>106, 'message' => "MobileNo. is empty") ;
            return response()->json($res);
        }
    }

    public function verifyCode(Request $request)
    { 
        $verifycode = $request->otpCode;
        $mob = $request->phone;

        if($mob !="" && $verifycode !="")
        {
            $where = ['phone' => $mob, 'phone_verify_code' => $verifycode];
            $users = Users::where($where)->count();
            $busers = BusinessUser::where($where)->count();
            $unregistered_users = MobileVerification::where($where)->count();

            if($users > 0) {
                $curtime = Users::where($where)->whereRaw('updated_at >= now() - INTERVAL 1 HOUR')->count();
                if($curtime > 0){
                    $code_update =  Users::where($where)->update(['phone_verify_status' => 1]);
                    $res =  array('status' => 1,'error-code'=>0, 'message' => "Mobile has been verified.") ;
                    return response()->json($res);
                }else{
                    $res =  array('status' => 0,'error-code'=>806, 'message' => "OTP has been expiered.") ;
                    return response()->json($res);
                }
            }elseif($busers > 0){
                $curtime = BusinessUser::where($where)->whereRaw('updated_at >= now() - INTERVAL 1 HOUR')->count();
                if($curtime > 0){
                    BusinessUser::where($where)->update(['phone_verify_status' => 1]);
                    $user_id =  BusinessUser::where($where)->value('id');
                    Profile::where('user_id',$user_id)->update(['visibility' => 1]);
                    $res =  array('status' => 1,'error-code'=>0, 'message' => "Mobile has verified.") ;
                    return response()->json($res);
                }else{
                    $res =  array('status' => 0,'error-code'=>806, 'message' => "OTP has been expiered.") ;
                    return response()->json($res);
                }
            }elseif($unregistered_users > 0){
                $curtime = MobileVerification::where($where)->whereRaw('updated_at >= now() - INTERVAL 1 HOUR')->count();
                if($curtime > 0){
                    MobileVerification::where($where)->update(['phone_verify_status' => 1]);
                    $res =  array('status' => 1,'error-code'=>0, 'message' => "Mobile has verified.") ;
                    return response()->json($res);
                }else{
                    $res =  array('status' => 0,'error-code'=>806, 'message' => "OTP has been expiered.") ;                    
                    return response()->json($res);
                }
            }else{
                $res =  array('status' => 0,'error-code'=>404, 'message' => "Mobile/OTP is invalid.") ;
                return response()->json($res);
            }
        }else{
            $res =  array('status' => 0,'error-code'=>206, 'message' => "Mobile/OTP is empty") ;
            return response()->json($res);
        }
       
    }



}
