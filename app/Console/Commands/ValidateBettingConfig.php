<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateBettingConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betting:validate-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate betting configuration settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Validating betting configuration...');
        
        // Check basic settings
        $this->line('Min outcomes: ' . config('betting.min_outcomes', 'NOT SET'));
        $this->line('Max outcomes: ' . config('betting.max_outcomes', 'NOT SET'));
        
        // Check bet amounts
        $allowedBets = config('betting.allowed_min_bets', []);
        $this->line('Allowed min bets: ' . implode(', ', $allowedBets));
        $this->line('Max bet multiplier: ' . config('betting.max_bet_multiplier', 'NOT SET'));
        $this->line('Max BON amount: ' . number_format(config('betting.max_bon_amount', 0)));
        
        // Check time settings
        $this->line('Min duration (minutes): ' . config('betting.min_duration_minutes', 'NOT SET'));
        $this->line('Max duration (days): ' . config('betting.max_duration_days', 'NOT SET'));
        $this->line('Default duration (hours): ' . config('betting.default_duration_hours', 'NOT SET'));
        
        // Check display settings
        $this->line('Items per page: ' . config('betting.items_per_page', 'NOT SET'));
        $this->line('Show odds: ' . (config('betting.show_odds', false) ? 'Yes' : 'No'));
        $this->line('Show expected payout: ' . (config('betting.show_expected_payout', false) ? 'Yes' : 'No'));
        $this->line('Anonymous betting allowed: ' . (config('betting.anonymous_betting_allowed', false) ? 'Yes' : 'No'));
        
        // Check cache settings
        $this->line('Cache duration (minutes): ' . config('betting.cache_duration_minutes', 'NOT SET'));
        
        // Check rate limiting
        $this->line('Max bets per day: ' . config('betting.rate_limiting.create_bets_per_day', 'NOT SET'));
        $this->line('Max entries per hour: ' . config('betting.rate_limiting.bet_entries_per_hour', 'NOT SET'));
        
        // Check validation limits
        $this->line('Max bet name length: ' . config('betting.validation.bet_name_max_length', 'NOT SET'));
        $this->line('Max description length: ' . config('betting.validation.bet_description_max_length', 'NOT SET'));
        $this->line('Max outcome name length: ' . config('betting.validation.outcome_name_max_length', 'NOT SET'));
        
        // Check payout settings
        $this->line('House edge: ' . (config('betting.payout.house_edge', 0) * 100) . '%');
        
        $this->newLine();
        $this->info('Configuration validation complete!');
        
        return Command::SUCCESS;
    }
}
