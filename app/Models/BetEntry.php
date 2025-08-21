<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BetEntry.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property int                             $bet_id
 * @property int                             $bet_outcome_id
 * @property string                          $amount
 * @property bool                            $anon
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class BetEntry extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bet_entries';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array{amount: 'decimal:2', anon: 'bool'}
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'anon'   => 'bool',
        ];
    }

    /**
     * Belongs to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'username' => 'System',
            'id'       => User::SYSTEM_USER_ID,
        ]);
    }

    /**
     * Belongs to a bet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Bet, $this>
     */
    public function bet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bet::class, 'bet_id');
    }

    /**
     * Belongs to an outcome.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<BetOutcome, $this>
     */
    public function outcome(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BetOutcome::class, 'bet_outcome_id');
    }
}
