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

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->boolean('can_bet')->default(false)->after('can_upload');
            $table->boolean('can_create_bet')->default(false)->after('can_bet');
            $table->boolean('can_close_bet')->default(false)->after('can_create_bet');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_bet')->nullable()->after('can_upload');
            $table->boolean('can_create_bet')->nullable()->after('can_bet');
            $table->boolean('can_close_bet')->nullable()->after('can_create_bet');
        });

        DB::table('users')->update([
            'can_bet' => null,
            'can_create_bet' => null,
            'can_close_bet' => null,
        ]);

        DB::table('groups')
            ->whereNotIn('slug', [
                'validating',
                'guest',
                'banned',
                'bot',
                'leech',
                'disabled',
                'pruned',
            ])
            ->update([
                'can_bet' => true,
                'can_create_bet' => true,
                'can_close_bet' => true,
            ]);
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['can_bet', 'can_create_bet', 'can_close_bet']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_bet', 'can_create_bet', 'can_close_bet']);
        });
    }
};
