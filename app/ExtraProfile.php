<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtraProfile extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'profile_extra';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['profile_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
