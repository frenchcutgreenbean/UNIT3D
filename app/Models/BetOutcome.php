<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     */
    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }

    /**
     * Has many entries (bets placed on this outcome).
     */
    public function entries()
    {
        return $this->hasMany(BetEntry::class);
    }
}
