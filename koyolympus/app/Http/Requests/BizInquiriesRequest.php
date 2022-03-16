<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BizInquiriesRequest extends FormRequest
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
            'name' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'opinion' => 'required|string|max:1000',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response['status'] = 400;
        $response['statusText'] = 'Failed validation.';
        $response['errors'] = $validator->errors();
        throw new HttpResponseException(response()->json($response, 400));
    }
}
