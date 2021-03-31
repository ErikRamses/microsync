<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return Config::get('boilerplate.user_save.validation_rules');

            case 'PUT':
                $validations = [
                    'name' => 'sometimes|required|string|max:255',
                    'username' => [
                        'sometimes',
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('users', 'username')->ignore($this->id),
                    ],
                    'email' => [
                        'sometimes',
                        'required',
                        'string',
                        'email',
                        'max:255',
                        Rule::unique('users', 'email')->ignore($this->id),
                    ],
                    'password' => 'sometimes|required|string|min:6|max:255',
                    'role' => 'sometimes|required|numeric',
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
