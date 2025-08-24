<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\BetOutcome
 */
class BetOutcomeResource extends JsonResource
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
            'name' => $this->name,
            'total_stake' => $this->entries->sum('amount'),
            'total_entries' => $this->entries->count(),
            'is_winner' => $this->when(
                $this->bet && $this->bet->winner_outcome_id,
                $this->id === $this->bet->winner_outcome_id
            ),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Relationships
            'entries' => BetEntryResource::collection($this->whenLoaded('entries')),
        ];
    }
}
