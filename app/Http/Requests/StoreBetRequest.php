<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBetRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'closing_time' => 'nullable|date|after:now',
            'min_bet' => 'required|integer|in:1000,10000,100000',
            'outcomes' => 'required|array|min:2|max:5',
            'outcomes.*' => 'nullable|string|max:255',
            'is_open_ended' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'min_bet.in' => 'Minimum bet must be 1,000, 10,000, or 100,000 points.',
            'closing_time.after' => 'Closing time must be in the future.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $closingTime = $this->input('closing_time');
            $isOpenEnded = $this->boolean('is_open_ended');
            
            // If not open-ended, closing time is required
            if (!$isOpenEnded && !$closingTime) {
                $validator->errors()->add('closing_time', 'Closing time is required unless this is an open-ended bet.');
            }
            
            // Check if closing time is more than 5 years in the future
            if ($closingTime && !$isOpenEnded) {
                $fiveYearsFromNow = now()->addYears(5);
                if (\Carbon\Carbon::parse($closingTime)->gt($fiveYearsFromNow)) {
                    $validator->errors()->add('closing_time', 'Closing time cannot be more than 5 years in the future.');
                }
            }
            
            // If open-ended, closing time should be ignored
            if ($isOpenEnded && $closingTime) {
                // We'll just ignore the closing time in the controller, no error needed
            }
        });
    }
}
