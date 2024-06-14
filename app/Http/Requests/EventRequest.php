<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'required|date|date_format:Y-m-d\TH:i',
            'end' => 'required|date|date_format:Y-m-d\TH:i|after:start',
            'recurring_pattern' => 'nullable|array',
            'recurring_pattern.frequency' => 'required_with:recurring_pattern|string|in:daily,weekly,monthly,yearly',
            'recurring_pattern.repeat_until' => 'required_with:recurring_pattern|date|date_format:Y-m-d|after:start'
        ];
    }
}
