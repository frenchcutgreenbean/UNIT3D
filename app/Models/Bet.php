<?php

namespace App\Models;

use App\Enums\BetStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Bet
 *
 * @property int    $id
 * @property string $name
 * @property int    $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */

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
        'status' => BetStatus::class,
        'is_open_ended' => 'boolean',
        'is_concluded' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    /**
     * Belongs to a user (creator).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the total number of entries for this bet.
     *
     * @return int
     */
    public function getTotalEntriesAttribute(): int
    {
        return $this->entries()->count();
    }

    /**
     * Get the total pot size for this bet.
     *
     * @return float
     */
    public function getPotSizeAttribute(): float
    {
        return $this->entries()->sum('amount') ?? 0.0;
    }

    /**
     * Has many outcomes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<BetOutcome>
     */
    public function outcomes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BetOutcome::class);
    }
  /**
     * Polymorphic comments relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<Comment>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Has many entries (bets placed).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<BetEntry>
     */
    public function entries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BetEntry::class);
    } 

    /**
     * Get the last activity time for this bet.
     *
     * @return string|null
     */
    public function getActivityAttribute(): ?string
    {
        $lastEntryTime = $this->entries()->max('created_at');
        
        return $lastEntryTime ? \Carbon\Carbon::parse($lastEntryTime)->diffForHumans() : null;
    }

    /**
     * Belongs to the winning outcome.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<BetOutcome, $this>
     */
    public function winnerOutcome(): \Illuminate\Database\Eloquent\Relations\BelongsTo
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
     *
     * @return bool
     */
    public function isOpenForBetting(): bool
    {
        return $this->status === BetStatus::OPEN && 
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
