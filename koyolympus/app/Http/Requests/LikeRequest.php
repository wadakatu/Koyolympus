<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class LikeRequest extends FormRequest
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
            'id' => 'required|string|uuid',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $id = $this->get('id');
        Log::info("[Requested ID: $id] " . __('messages.PHOTO.INVALID_UUID'));
        abort(404);
    }
}
