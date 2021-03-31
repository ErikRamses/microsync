<?php

namespace App\Models;

use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;
    use CascadeSoftDeletes;
    protected $cascadeDeletes = ['beneficiaries', 'attachments', 'monthlyCuts', 'interest_rates'];

    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'date_init',
        'date_expire',
        'total_investment',
        'count_investment',
        'investor_name',
    ];

    /**
     * Get created by.
     */
    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }

    /**
     * Get updated by.
     */
    public function editor()
    {
        return $this->belongsTo('App\User', 'updated_by', 'id');
    }

    public function investor()
    {
        return $this->belongsTo('App\Models\Investor');
    }

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment');
    }

    public function beneficiaries()
    {
        return $this->hasMany('App\Models\ContractBeneficiary');
    }

    public function attachments()
    {
        return $this->hasMany('App\Models\Attachment');
    }

    public function bankData()
    {
        return $this->belongsTo('App\Models\InvestorBankData', 'bank_account_id');
    }

    public function getInvestorNameAttribute()
    {
        return ('Moral' == $this->investor->type) ? $this->investor->corporate_name : $this->investor->full_name;
    }

    public function getInterestPercentAttribute()
    {
        $isr = Config::getIsrValue();

        return ($this->rate_type) ? ($this->interest_rate + $isr) : $this->interest_rate;
    }

    public function getTotalInvestmentAttribute()
    {
        $now = Carbon::now('America/Monterrey');

        return $this->attachments()->where('cancelled_at', null)->where('date_expire', '>', date('Y-m-d', strtotime($now)))->sum('amount');
    }

    public function getCountInvestmentAttribute()
    {
        $now = Carbon::now('America/Monterrey');

        return $this->attachments()->where('cancelled_at', null)->where('date_expire', '>', date('Y-m-d', strtotime($now)))->count();
    }

    public function attachmentsCount()
    {
        $now = Carbon::now('America/Monterrey');

        return $this->attachments()->where('date_expire', '>', date('Y-m-d', strtotime($now)))->count();
    }

    public function monthlyCuts()
    {
        return $this->hasMany('App\Models\MonthlyCutAttachment');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\ContractType', 'contract_type_id', 'id');
    }

    public function payment_method()
    {
        return $this->belongsTo('App\Models\PaymentMethod', 'payment_method_id', 'id');
    }

    public function frequency()
    {
        return $this->belongsTo('App\Models\Frequency', 'frequency_id', 'id');
    }

    public function interest_rates()
    {
        return $this->hasMany('App\Models\ContractRate');
    }
}
