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
        return can_create_bet($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $allowedMinBets = config('betting.allowed_min_bets', [1000, 10000, 100000]);
        $maxOutcomes = config('betting.max_outcomes', 5);
        $minOutcomes = config('betting.min_outcomes', 2);
        $maxNameLength = config('betting.validation.bet_name_max_length', 255);
        $maxDescLength = config('betting.validation.bet_description_max_length', 2000);
        $minDurationMinutes = config('betting.min_duration_minutes', 60);
        $maxDurationDays = config('betting.max_duration_days', 30);
        
        return [
            'name' => "required|string|max:{$maxNameLength}",
            'description' => "nullable|string|max:{$maxDescLength}",
            'closing_time' => [
                'nullable',
                'date',
                'after:' . now()->addMinutes($minDurationMinutes)->toDateTimeString(),
                'before:' . now()->addDays($maxDurationDays)->toDateTimeString(),
            ],
            'min_bet' => 'required|integer|in:' . implode(',', $allowedMinBets),
            'outcomes' => "required|array|min:{$minOutcomes}|max:{$maxOutcomes}",
            'outcomes.*' => 'nullable|string|max:' . config('betting.validation.outcome_name_max_length', 255),
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
