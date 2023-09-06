<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response as HttpResponse;
use \Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Client\Response;
use \Illuminate\Validation\ValidationException;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'name' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = new HttpResponse([
            'errors' => $validator->errors(),
        ],HttpResponse::HTTP_UNPROCESSABLE_ENTITY);

        throw (new ValidationException($validator,$response));
    }
}
