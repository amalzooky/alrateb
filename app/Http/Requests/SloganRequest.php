<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SloganRequest extends FormRequest
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
            'slog' => 'required_without:id|mimes:jpg,jpeg,png',
            'slog.*.name' => 'required|string|max:100',
            'slog.*.text' => 'required|string|max:100',
            'slog.*.active' => 'required',
            'slog.*.abbr' => 'required',
        ];
    }
}
