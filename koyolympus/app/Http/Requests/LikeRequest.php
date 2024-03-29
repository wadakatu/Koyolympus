<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Lang;
use Log;

class LikeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'required|string|uuid',
        ];
    }

    /**
     * バリデーション失敗時の処理
     *
     * @param  Validator  $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        /** @var string $id */
        $id = $this->get('id');
        Log::info("[Requested ID: $id]");
        Log::info(Lang::get('messages.PHOTO.INVALID_UUID'));
        abort(404);
    }
}
