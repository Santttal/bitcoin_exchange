<?php

namespace App\Http\Requests;

use App\Models\Fiat;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

class OfferFormRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'fiat' => ['required', 'in:' . implode(',', Fiat::availableCurrencies())],
            'payment_method' => ['required', 'in:' . implode(',', PaymentMethod::all('id')->pluck('id')->toArray())],
            'margin' => ['required', 'numeric'],
            'min_fiat' => ['required', 'numeric'],
            'max_fiat' => ['required', 'numeric', 'gt:min_fiat'],
        ];
    }
}
