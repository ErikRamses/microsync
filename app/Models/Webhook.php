<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    public function shop()
    {
        return $this->hasOne('App\Models\Shop', 'id', 'shop_id');
    }
}
