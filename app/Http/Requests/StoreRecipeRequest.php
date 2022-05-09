<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreRecipeRequest extends FormRequest
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
            'name' => Rule::unique('recipes')->where(fn ($query) => $query->where('user_id', Auth::user()->id)),
            // 'name' => 'required|string',
            'ingredients' => 'required|array',
            'rating' => 'integer|between:0,5'
        ];
    }
}
