<?php

declare(strict_types=1);

namespace Modules\Order\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;
use Modules\Order\Enums\OrderStatus;

final class OrderUpdateRequest extends FormRequest
{
    protected $rules = [];

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
        $this->rules = $this->getRules();

        return $this->rules;
    }

    public function failedValidation(Validator $validator)
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
            'coupon_id' => 'nullable|exists:Modules\Ecommerce\Models\Coupon,id',
            'shop_id' => 'exists:Modules\Ecommerce\Models\Shop,id',
            'products' => 'array',
            'amount' => 'numeric',
            'paid_total' => 'numeric',
            'total' => 'numeric',
            'order_status' => ['required', new Enum(OrderStatus::class)],
            'customer_id' => 'exists:Modules\Ecommerce\Models\User,id',
            'payment_gateway' => 'string',
            'altered_payment_gateway' => 'nullable|string',
        ];
    }
}
