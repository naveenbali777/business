<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::post('register','ProcessController@register');

Route::post('login','ProcessController@login');

Route::get('logout','ProcessController@logout');

Route::get('checkUser','ProcessController@checkUser');

Route::get('email_sent','ProcessController@email_sent');

Route::get('verifyEmail','ProcessController@email_verification');

Route::get('profile','ProfileController@show');

Route::get('oneprofile','ProfileController@show_profile');

Route::get('profile_elements','ProfileController@profile_elements');

Route::get('create_profile','ProfileController@create_profile');

Route::post('profile_create','ProfileController@create');

Route::post('editBusinessHours','ProfileController@edit_business_hours');

Route::post('updateBusinessHours','ProfileController@update_business_hours');

Route::post('editBusinessPrice','ProfileController@edit_business_price');

Route::post('updateBusinessPrice','ProfileController@update_business_price');

Route::post('updateProfile','ProfileController@update_profile_info');

Route::post('address_save','ProfileController@address_save');

Route::get('address_create','ProfileController@address_create');

Route::post('address_update/{id}','ProfileController@address_update');

Route::get('address_edit/{id}','ProfileController@address_edit');

Route::get('availability','ProfileController@availability');

Route::post('business_hours','ProfileController@business_hours');

Route::post('user_profile','ProfileController@register');

Route::get('service_elements','ServicesController@elements');

Route::get('image_show','ImageController@show');

Route::match(array('GET','POST'),'image_upload', 'ImageController@upload');

Route::delete('delete_img/{id}','ImageController@delete_img');

Route::post('booking','BookingController@create');

Route::post('save_review','ReviewController@insert');

Route::get('review','ReviewController@show');

Route::post('signup','UsersController@create');

Route::post('signin','UsersController@show');

Route::post('userdetails','UsersController@details');

Route::post('saveMessage','UsersController@message_save');


//Business Backend Routs

Route::get('allreviews','BusinessBackend@reviews');

Route::get('allbookings','BusinessBackend@bookdetails');

Route::post('allmessages','BusinessBackend@messages');

Route::post('savetour','BusinessBackend@touraddress');

Route::post('bookingconfirm','BusinessBackend@bookconfirm');

Route::post('bookingcalendar','BusinessBackend@bookingcal');

Route::post('manualbooking','BusinessBackend@manual_booking');

Route::get('verifyphone','VerifyPhone@verifyPhone');

Route::post('tourlist','BusinessBackend@tourAddressList');

Route::post('deleteTour','BusinessBackend@deleteTourAddress');

Route::post('sendOTP','VerifyPhone@sendOTP');

Route::post('verifyPhone','VerifyPhone@verifyCode');

Route::post('checkPhone','ProcessController@verifyPhoneAccount');

Route::post('visibility','BusinessBackend@visibility');

Route::post('dashboardProfile','BusinessBackend@dashboard_data');

Route::post('upcomingTours','BusinessBackend@upcoming_tours');

Route::post('reviewResponce','BusinessBackend@save_review_reply');

Route::post('graphValues','BusinessBackend@graph_values');

Route::post('messageRead','BusinessBackend@message_read');
