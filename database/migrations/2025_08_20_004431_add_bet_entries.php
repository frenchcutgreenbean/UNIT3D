<?php
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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('bet_entries', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('bet_id');
            $table->unsignedInteger('bet_outcome_id');
            $table->unsignedInteger('user_id');
            $table->decimal('amount', 12, 2);
            $table->boolean('anon')->default(false);
            $table->decimal('payout', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('bet_id')->references('id')->on('bets')->onDelete('cascade');
            $table->foreign('bet_outcome_id')->references('id')->on('bet_outcomes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
