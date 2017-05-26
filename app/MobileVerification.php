<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/*it has used for unregistered user to mobile verification */
class MobileVerification extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'mobile_verification_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['phone', 'phone_verify_status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
