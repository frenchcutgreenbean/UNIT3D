<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bets';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'closing_time' => 'datetime',
        'status' => 'string',
        'is_open_ended' => 'boolean',
        'is_concluded' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    /**
     * Belongs to a user (creator).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Get the total number of entries for this bet.
     */
    public function getTotalEntriesAttribute(): int
    {
        return $this->entries()->count();
    }

    /**
     * Get the total pot size for this bet.
     */
    public function getPotSizeAttribute(): float
    {
        return $this->entries()->sum('amount') ?? 0.0;
    }

    /**
     * Has many outcomes.
     */
    public function outcomes()
    {
            return $this->hasMany(BetOutcome::class);
        }

    /**
     * Has many entries (bets placed).
     */
    public function entries()
    {
        return $this->hasMany(BetEntry::class);
    } 

    /**
     * Get the last activity time for this bet.
     */
    public function getActivityAttribute(): ?string
    {
        $lastEntryTime = $this->entries()->max('created_at');
        
        return $lastEntryTime ? \Carbon\Carbon::parse($lastEntryTime)->diffForHumans() : null;
    }

    /**
     * Belongs to the winning outcome.
     */
    public function winnerOutcome()
    {
        return $this->belongsTo(BetOutcome::class, 'winner_outcome_id');
    }

    /**
     * Check if the bet can be edited.
     */
    public function canBeEdited(): bool
    {
        return $this->entries()->count() === 0;
    }

    /**
     * Check if the bet is open for betting.
     */
    public function isOpenForBetting(): bool
    {
        return $this->status === 'open' && 
               ($this->is_open_ended || !$this->closing_time || now()->lt($this->closing_time));
    }

    /**
     * Check if the bet has expired.
     */
    public function hasExpired(): bool
    {
        return !$this->is_open_ended && 
               $this->closing_time && 
               now()->gt($this->closing_time);
    }

}
