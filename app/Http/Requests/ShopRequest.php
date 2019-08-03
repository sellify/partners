<?php

namespace App\Http\Requests;

use App\App;
use App\Shop;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopRequest extends FormRequest
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
    public function rules($values = [])
    {
        return [
            'app_id' => [
                'required',
                Rule::exists((new App())->getTable(), (new App())->getKeyName()),
            ],
            'user_id' => [
                'required',
                Rule::exists((new User())->getTable(), (new User())->getKeyName()),
            ],
            'shopify_domain' => [
                'required',
                Rule::unique((new Shop())->getTable(), 'shopify_domain')
                    ->where(function ($query) use ($values) {
                        $query->where('app_id', isset($values['app_id']) ? $values['app_id'] : $this->request->get('app_id'));
                    }),
            ],
        ];
    }

    public function messages()
    {
        return [
            'app_id.exists'         => "The app doesn't exists or you're not the owner.",
            'user_id.exists'        => "This affiliate doesn't exists.",
            'shopify_domain.unique' => 'This shop is already referred for the selected app.',
        ];
    }
}
