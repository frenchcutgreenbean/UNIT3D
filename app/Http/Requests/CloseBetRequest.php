<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseBetRequest extends FormRequest
{
    public function authorize(): bool
    {
        // allow moderators or users allowed by can_close_bet (owner override etc.)
        return can_close_bet($this->user(), $this->route('bet'));
    }

    public function rules(): array
    {
        return [
            'winner_outcome_id' => [
                'required',
                'exists:bet_outcomes,id',
                function ($attribute, $value, $fail) {
                    $bet = $this->route('bet');
                    if ($bet && !$bet->outcomes()->where('id', $value)->exists()) {
                        $fail('The selected outcome does not belong to this bet.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'winner_outcome_id.required' => 'Please select a winning outcome.',
            'winner_outcome_id.exists' => 'The selected outcome is invalid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $bet = $this->route('bet');
            
            // Check if bet is already completed
            if ($bet && $bet->status === 'completed') {
                $validator->errors()->add('winner_outcome_id', 'This bet has already been completed.');
            }
        });
    }
}
