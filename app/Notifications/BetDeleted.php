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

class BetDeleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $betName,
        public float $amount
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        // Defensive check for empty bet name
        $betName = $this->betName ?: 'Unknown Bet';
        $amount = max(0, $this->amount); // Ensure amount is never negative
        
        return [
            'type'       => 'bet_deleted',
            'title'      => 'Bet Cancelled & Refunded',
            'body'       => sprintf('Bet "%s" was deleted and you were refunded %s BP.', $betName, number_format($amount, 2)),
            'amount'     => $amount,
            'result'     => 'deleted',
            'url'        => null,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}