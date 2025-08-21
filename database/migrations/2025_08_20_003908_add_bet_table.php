<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('bets', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamp('closing_time')->nullable();
            $table->integer('min_bet')->default(0);
            $table->string('status')->default('open');
            $table->unsignedInteger('user_id');
            $table->boolean('is_open_ended')->default(false);
            $table->boolean('is_concluded')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
            $table->unsignedInteger('winner_outcome_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};