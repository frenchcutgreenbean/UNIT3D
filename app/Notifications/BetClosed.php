<?php

declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Notifications;


use App\Models\Bet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BetClosed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $result,      // 'won'|'lost'|'cancelled'
        public Bet $bet,
        public ?float $amount = null // user's payout (null if none)
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'bet_closed',
            'bet_id'     => $this->bet->id,
            'title'      => $this->result === 'won' ? 'Bet Won!' : ($this->result === 'cancelled' ? 'Bet Cancelled' : 'Bet Lost'),
            'body'       => $this->result === 'won'
                ? sprintf('You won %s BP on "%s".', number_format($this->amount ?? 0, 2), $this->bet->name)
                : ($this->result === 'cancelled'
                    ? sprintf('The bet "%s" was cancelled.', $this->bet->name)
                    : sprintf('You lost on "%s".', $this->bet->name)
                ),
            'amount'     => $this->amount,
            'result'     => $this->result,
            'url'        => "/bets/{$this->bet->id}",
            'created_at' => now()->toDateTimeString(),
        ];
    }
}