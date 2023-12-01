<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class StoreProductRequest extends FormRequest
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
            'name' => 'required',
            'thumbnail' => 'required',
            'category_id' => 'required',
            'supplier_id' => 'required',
            'price_old' => 'required',
            'quantity' => 'required',
            'material_id' => 'required',
            'status' => 'required',
            'shape_id' => 'required',
            'color' => 'required'
        ];
    }

    // public function messages()
    // {
    //     return [
    //         'email.required' => 'Vui lòng nhập email',
    //         'email.email' => 'Vui lòng nhập đúng định dạng email',
    //         'email.unique' => 'Địa chỉ email này đã tồn tại',
    //         'fullname.required' => 'VUi lòng nhập trường này',
    //         'passưord.required' => 'Vui lòng nhập trương này ' ,
    //     ];
    // }

    protected function failedValidation(Validator $validator)
    {
        $response = new Response([
            'errors' => $validator->errors(),
        ],Response::HTTP_UNPROCESSABLE_ENTITY);

        throw (new ValidationException($validator,$response));
    }
}
