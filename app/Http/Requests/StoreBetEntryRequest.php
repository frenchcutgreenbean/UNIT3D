<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreBetEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $bet = $this->route('bet');

        if (! $user || ! $bet) {
            return false;
        }

        // user must be allowed to bet and bet must be open
        if (! can_bet($user, $bet)) {
            return false;
        }

        if ($bet->status !== 'open') {
            return false;
        }

        if ($bet->closing_time && now()->gt($bet->closing_time)) {
            return false;
        }

        return true;
    }

    public function rules(Request $request): array
    {
        $bet = $request->route('bet');
        $minBet = $bet ? $bet->min_bet : 1000;
        $maxBet = $bet ? $bet->min_bet * 10 : 10000;
        
        return [
            'bet_outcome_id' => [
                'required',
                'exists:bet_outcomes,id',
                function ($attribute, $value, $fail) use ($bet) {
                    if ($bet && !$bet->outcomes()->where('id', $value)->exists()) {
                        $fail('The selected outcome does not belong to this bet.');
                    }
                },
            ],
            'amount' => [
                'required',
                'numeric',
                'min:' . $minBet,
                'max:' . min($maxBet, $request->user()->seedbonus ?? 0),
            ],
            'anon' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Bet amount must be at least :min points.',
            'amount.max' => 'Bet amount cannot exceed :max points or your current bonus points.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $bet = $this->route('bet');
            $user = $this->user();
            
            // Check if user has already bet on this bet
            if ($bet && $user && $bet->entries()->where('user_id', $user->id)->exists()) {
                $validator->errors()->add('bet_outcome_id', 'You have already placed a bet on this.');
            }
            
            // Check if bet is still open
            if ($bet && $bet->status !== 'open') {
                $validator->errors()->add('bet_outcome_id', 'This bet is no longer accepting entries.');
            }
            
            // Check if bet hasn't closed yet
            if ($bet && $bet->closing_time && now()->gt($bet->closing_time)) {
                $validator->errors()->add('bet_outcome_id', 'This bet has already closed.');
            }
        });
    }
}
