<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StationRequest extends FormRequest
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
            'departure' => 'required',
            'station' => 'required|string',
            'train' => 'required|string'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'departure' => [
                'date' => Str::of($this->departure)
                    ->before('T')
                    ->replaceArray('-', ['', ''])
                    ->substr(2),
                'time' => Str::of($this->departure)
                    ->after('T')
                    ->substr(0, 2)
            ],
        ]);
    }
}
