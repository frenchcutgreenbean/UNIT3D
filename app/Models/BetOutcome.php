<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\BetOutcome
 *
 * @property int                             $id
 * @property int                             $bet_id
 * @property string                          $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class BetOutcome extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bet_outcomes';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Belongs to a bet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Bet, $this>
     */
    public function bet(): BelongsTo
    {
        return $this->belongsTo(Bet::class);
    }

    /**
     * Has many entries (bets placed on this outcome).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<BetEntry>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(BetEntry::class);
    }
}
