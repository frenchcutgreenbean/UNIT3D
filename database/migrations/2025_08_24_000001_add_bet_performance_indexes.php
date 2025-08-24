<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->index('status');
            $table->index('user_id');
            $table->index('closing_time');
            $table->index(['status', 'closing_time']);
        });

        Schema::table('bet_entries', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('bet_id');
            $table->index('bet_outcome_id');
            $table->index(['bet_id', 'user_id']);
        });

        Schema::table('bet_outcomes', function (Blueprint $table) {
            $table->index('bet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['closing_time']);
            $table->dropIndex(['status', 'closing_time']);
        });

        Schema::table('bet_entries', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['bet_id']);
            $table->dropIndex(['bet_outcome_id']);
            $table->dropIndex(['bet_id', 'user_id']);
        });

        Schema::table('bet_outcomes', function (Blueprint $table) {
            $table->dropIndex(['bet_id']);
        });
    }
};
