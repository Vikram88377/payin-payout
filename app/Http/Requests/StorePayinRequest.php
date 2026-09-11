<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Merchant;
use Illuminate\Contracts\Validation\Validator;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Response;

class StorePayinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
            'merchant_id' => ['required', 'integer', 'exists:merchants,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'remarks' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'merchant_id.exists' => 'Merchant does not exist.',
            'amount.min' => 'Amount must be at least 1.',
        ];
    }

     public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $merchantId = $this->input('merchant_id');
 
            if (! $merchantId) {
                return;
            }
 
            $merchant = Merchant::find($merchantId);
 
            if ($merchant && ! $merchant->isActive()) {
                $validator->errors()->add('merchant_id', 'Merchant is not active.');
            }
        });
    }
 
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            Response::json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
