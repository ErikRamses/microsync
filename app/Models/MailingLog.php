<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailingLog extends Model
{
    protected $table = 'mailing_log';

    public function mailing()
    {
        return $this->belongsTo('App\Mailing');
    }

    public function investor()
    {
        return $this->hasOne('App\Models\Investor', 'id', 'investor_id');
    }

    public function monthly_cut()
    {
        return $this->hasOne('App\Models\MonthlyCut', 'id', 'monthly_cut_id');
    }
}
