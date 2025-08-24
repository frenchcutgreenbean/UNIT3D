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

namespace App\Providers;

use App\Listeners\AchievementUnlocked;
use App\Listeners\LoginListener;
use App\Listeners\RegisteredListener;
use Assada\Achievements\Event\Unlocked;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            RegisteredListener::class,
        ],
        Login::class => [
            LoginListener::class,
        ],
        Unlocked::class => [
            AchievementUnlocked::class,
        ],
        
        // Betting Events
        \App\Events\BetCreated::class => [
            \App\Listeners\SendBetCreatedChatMessage::class,
        ],
        \App\Events\BetClosed::class => [
            \App\Listeners\ProcessBetClosedNotifications::class,
        ],
        \App\Events\BetCancelled::class => [
            // Add listeners for bet cancellation if needed
        ],
        \App\Events\BetEntryCreated::class => [
            // Add listeners for bet entry creation if needed
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
        \App\Models\Bet::observe(\App\Observers\BetObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
