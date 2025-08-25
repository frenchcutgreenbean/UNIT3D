<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Bet;
use App\Models\BetOutcome;
use App\Models\BetEntry;
use App\Enums\BetStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class BetTestSeeder extends Seeder
{
    public function run(): void
    {

        // Create or reuse a test creator user (uses username field like UserSeeder)
        $creator = User::firstOrCreate(
            ['username' => 'bet-test-creator'],
            [
                'email'             => 'bet-test-creator@example.com',
                'email_verified_at' => now(),
                'group_id'          => 4,
                'password'          => Hash::make('password'),
                'seedbonus'         => 100000,
                'passkey'           => md5(random_bytes(60)),
                'rsskey'            => md5(random_bytes(60)),
                'api_token'         => Str::random(100),
            ]
        );

        // Create additional bettor users (use username, idempotent)
        $bettors = [];
        for ($i = 1; $i <= 3; $i++) {
            $username = "bet-test-user{$i}";
            $email = "bet-test-user{$i}@example.com";

            $bettors[] = User::firstOrCreate(
                ['username' => $username],
                [
                    'email'             => $email,
                    'email_verified_at' => now(),
                    'group_id'          => 4,
                    'password'          => Hash::make('password'),
                    'seedbonus'         => 20000,
                    'passkey'           => md5(random_bytes(60)),
                    'rsskey'            => md5(random_bytes(60)),
                    'api_token'         => Str::random(100),
                ]
            );
        }

        // Number of bets to create
        $totalBets = 15;

        $minOutcomes = config('betting.min_outcomes', 2);
        $maxOutcomes = config('betting.max_outcomes', 4);
        $defaultMinBet = config('betting.default_min_bet', 1000);

        // Determine starting index so repeated runs create new uniquely numbered bets
        $existingMax = Bet::where('name', 'like', 'Automated Test Bet #%')
            ->get()
            ->map(function (Bet $bet) {
                if (preg_match('/#(\d+)$/', $bet->name, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->max() ?? 0;

        $start = $existingMax + 1;
        $end = $existingMax + $totalBets;

        for ($b = $start; $b <= $end; $b++) {
            $betName = "Automated Test Bet #{$b}";

            $bet = Bet::firstOrCreate(
                ['name' => $betName],
                [
                    'description'    => "This is automated test bet number {$b}.",
                    'closing_time'   => now()->subDays(1 + ($b % 3)), // stagger expiries
                    'min_bet'        => $defaultMinBet,
                    'user_id'        => $creator->id,
                    'status'         => BetStatus::OPEN,
                    'is_open_ended'  => false,
                ]
            );

            // Ensure outcomes exist (idempotent) - pick a count between min and max
            $outcomeCount = rand($minOutcomes, max($minOutcomes, $maxOutcomes));
            for ($i = 1; $i <= $outcomeCount; $i++) {
                BetOutcome::firstOrCreate(
                    ['bet_id' => $bet->id, 'name' => "Outcome {$i}"],
                    ['slug' => Str::slug("Outcome {$i}")]
                );
            }

            // Create a few entries if none exist for this bet
            if ($bet->entries()->count() === 0) {
                $outcomes = $bet->outcomes()->get();
                foreach ($bettors as $idx => $user) {
                    // choose an outcome in round-robin and vary amounts
                    $outcome = $outcomes->get($idx % $outcomes->count());
                    BetEntry::create([
                        'bet_id'         => $bet->id,
                        'bet_outcome_id' => $outcome->id,
                        'user_id'        => $user->id,
                        'amount'         => $defaultMinBet * (1 + (($b + $idx) % 4)), // varying amounts
                        'payout'         => 0,
                    ]);
                }
                $this->command->info("Created sample entries for test bet: {$betName} (id: {$bet->id}).");
            } else {
                $this->command->info("Test bet already has entries; skipping entry creation for: {$betName} (id: {$bet->id}).");
            }
        }

        $this->command->info("BetTestSeeder completed: {$totalBets} test bets ensured.");
    }
}