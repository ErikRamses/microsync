<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mailing extends Model
{
    protected $table = 'mailing';

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function logs()
    {
        return $this->hasMany('App\MailingLog', 'mailing_id', 'id');
    }
}
