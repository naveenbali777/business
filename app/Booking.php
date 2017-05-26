<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'booking';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['profile_id', 'user_id','user_email'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
