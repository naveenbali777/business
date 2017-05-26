<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TouringAddress extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'business_touring_address';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['profile_id', 'address'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
