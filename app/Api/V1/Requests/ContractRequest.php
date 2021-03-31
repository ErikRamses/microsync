<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractRequest extends FormRequest
{
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return Config::get('boilerplate.contract_save.validation_rules');

            case 'PUT':

                $validations = [
                    'date_init' => 'required|date_format:"Y-m-d"',
                    'interest_rate' => 'required',
                    'cut_day' => 'required',
                    'frequency_id' => 'required|exists:frequencies,id',
                    'contract_type_id' => 'required|exists:contract_types,id',
                    'investor_id' => 'required|exists:investors,id',
                    'mixed_rate' => 'required_if:contract_type_id,2',
                    'folio' => [
                        'sometimes',
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('contracts', 'folio')->ignore($this->id),
                    ],
                ];

                return Config::get($validations);

            case 'PATCH':
            case 'GET':
            case 'DELETE':

                return [];

            default:break;
        }
    }

    public function authorize()
    {
        return true;
    }
}
