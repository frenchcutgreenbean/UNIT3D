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

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->boolean('can_bet')->default(true)->after('can_upload');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_bet')->default(true)->after('can_upload');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('can_bet');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_bet');
        });
    }
};
