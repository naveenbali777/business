<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessHours extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'business_hours';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['profile_id', 'day'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
