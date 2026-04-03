<?php

declare(strict_types=1);

namespace Modules\Order\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckoutVerifyRequest extends FormRequest
{
    protected $rules = [];

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
        return $this->getRules();
    }

    /**
     * Get the error messages that apply to the request parameters.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'products.required' => 'Product field is required',
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }

    /**
     * General validation rules
     *
     * @return array
     */
    protected function getRules()
    {
        return [
            'amount' => 'required|numeric',
            'customer_id' => 'nullable|exists:Modules\User\Models\User,id',
            'products' => 'required|array',
            'billing_address' => 'array',
            'shipping_address' => 'array',
        ];
    }
}
