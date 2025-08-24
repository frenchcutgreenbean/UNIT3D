<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Betting System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all the configuration options for the betting system.
    | You can modify these values to customize the betting functionality.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Bet Creation Settings
    |--------------------------------------------------------------------------
    */
    
    'min_outcomes' => 2,
    'max_outcomes' => 5,
    
    /*
    |--------------------------------------------------------------------------
    | Bet Amount Settings
    |--------------------------------------------------------------------------
    */
    
    'allowed_min_bets' => [1000, 10000, 100000],
    'default_min_bet' => 1000,
    'max_bet_multiplier' => 10, // Maximum bet = min_bet * this multiplier
    'max_bon_amount' => 10000000, // Maximum BON amount for individual bet
    
    /*
    |--------------------------------------------------------------------------
    | Time Settings
    |--------------------------------------------------------------------------
    */
    
    'min_duration_minutes' => 60, // Minimum 1 hour from creation to closing
    'max_duration_days' => 30, // Maximum 30 days duration
    'default_duration_hours' => 24, // Default duration if not specified
    
    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    
    'notifications' => [
        'chat_announcements' => true,
        'user_notifications' => true,
        'email_notifications' => false,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    
    'cache_duration_minutes' => 5, // How long to cache permission checks
    
    /*
    |--------------------------------------------------------------------------
    | Display Settings
    |--------------------------------------------------------------------------
    */
    
    'items_per_page' => 10,
    'show_odds' => true, // Show calculated odds on bet outcomes
    'show_expected_payout' => true, // Show expected payout before betting closes
    'anonymous_betting_allowed' => true,
    
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    
    'rate_limiting' => [
        'create_bets_per_day' => 5, // Max bets a user can create per day
        'bet_entries_per_hour' => 20, // Max bet entries per hour
    ],
    
    'payout' => [
        'house_edge' => 0.05, // 5% house edge
    ],
    
    'validation' => [
        'bet_name_max_length' => 255,
        'bet_description_max_length' => 2000,
        'outcome_name_max_length' => 255,
    ],
];
