<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    /**
     * Get Shops Model.
     */
    public function webhooks()
    {
        return $this->belongsTo('App\Models\Webhook');
    }
}
