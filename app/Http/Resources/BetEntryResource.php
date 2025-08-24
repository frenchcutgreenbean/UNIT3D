<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\BetEntry
 */
class BetEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'payout' => $this->payout,
            'is_anonymous' => $this->anon,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Relationships
            'user' => $this->when(
                !$this->anon || ($request->user() && $request->user()->can('viewAny', \App\Models\User::class)),
                new \App\Http\Resources\UserResource($this->whenLoaded('user'))
            ),
            'outcome' => new BetOutcomeResource($this->whenLoaded('outcome')),
        ];
    }
}
