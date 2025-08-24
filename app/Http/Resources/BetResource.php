<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Bet
 */
class BetResource extends JsonResource
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
            'description' => $this->description,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'min_bet' => $this->min_bet,
            'pot_size' => $this->pot_size,
            'total_entries' => $this->total_entries,
            'is_open_ended' => $this->is_open_ended,
            'closing_time' => $this->closing_time?->toISOString(),
            'is_open_for_betting' => $this->isOpenForBetting(),
            'has_expired' => $this->hasExpired(),
            'can_be_edited' => $this->canBeEdited(),
            'activity' => $this->activity,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Relationships
            'creator' => new \App\Http\Resources\UserResource($this->whenLoaded('user')),
            'outcomes' => BetOutcomeResource::collection($this->whenLoaded('outcomes')),
            'winner_outcome' => new BetOutcomeResource($this->whenLoaded('winnerOutcome')),
            
            // Permissions for current user
            'permissions' => $this->when($request->user(), [
                'can_bet' => $request->user() ? $request->user()->can('bet', $this->resource) : false,
                'can_edit' => $request->user() ? $request->user()->can('update', $this->resource) : false,
                'can_delete' => $request->user() ? $request->user()->can('delete', $this->resource) : false,
                'can_close' => $request->user() ? $request->user()->can('close', $this->resource) : false,
                'can_cancel' => $request->user() ? $request->user()->can('cancel', $this->resource) : false,
            ]),
        ];
    }
}
