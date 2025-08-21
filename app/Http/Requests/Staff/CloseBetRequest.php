<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class CloseBetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->group->is_modo;
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
            
            // Check if bet has any entries (removed the restriction - mods can close bets with no entries)
            // This allows mods to close/cancel bets even without entries if needed
        });
    }
}
