<?php

namespace App\Http\Controllers;


use App\User;
use App\Services;
use Validator;
use App\Http\Requests;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    /**-
     * 
     * @return \Illuminate\Http\Response
     */
    public function elements()
    {        
        $all_services = Services::all();
        return response()->json($all_services);
    }


}
