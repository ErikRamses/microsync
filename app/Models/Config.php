<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Config extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'value', 'type', 'description',
    ];

    protected $hidden = [
        'created_at', 'updated_at',
    ];

    public function updated_by()
    {
        return $this->belongsTo('App\User', 'updated_by', 'id');
    }

    public static function getTestingMode()
    {
        $value = 0;
        $config = Config::where('name', 'testing_mode')->first();
        if (isset($config)) {
            $value = intval($config->value);
        }

        return $value;
    }

    public static function getTestingEmail()
    {
        $value = 'cmedina@concentrico.com.mx';
        $config = Config::where('name', 'testing_email')->first();
        if (isset($config)) {
            $value = $config->value;
        }

        return $value;
    }

    public static function getIsrValue()
    {
        $value = 1.04;
        $config = Config::where('name', 'isr')->first();
        if (isset($config)) {
            $value = $config->value;
        }

        return $value;
    }

    public static function getSenderEmail()
    {
        $value = 'no-reply@concentricotests.com';
        $config = Config::where('name', 'sender_email')->first();
        if (isset($config)) {
            $value = $config->value;
        }

        return $value;
    }

    public static function getSenderName()
    {
        $value = 'Trinitas';
        $config = Config::where('name', 'sender_name')->first();
        if (isset($config)) {
            $value = $config->value;
        }

        return $value;
    }

    public static function getDisclaimer()
    {
        $text = '';
        $config = Config::where('name', 'disclaimer')->first();
        if (isset($config)) {
            $text = $config->value;
        }

        return $text;
    }
}
