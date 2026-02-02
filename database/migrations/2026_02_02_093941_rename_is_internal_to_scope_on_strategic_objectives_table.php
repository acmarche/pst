<?php

declare(strict_types=1);

use App\Enums\ActionScopeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('strategic_objectives', function (Blueprint $table) {
            $table->string('scope')->default(ActionScopeEnum::EXTERNAL->value)->after('is_internal');
        });

        DB::table('strategic_objectives')->where('is_internal', true)->update(['scope' => ActionScopeEnum::INTERNAL->value]);
        DB::table('strategic_objectives')->where('is_internal', false)->update(['scope' => ActionScopeEnum::EXTERNAL->value]);

        Schema::table('strategic_objectives', function (Blueprint $table) {
            $table->dropColumn('is_internal');
        });
    }

    public function down(): void
    {
        Schema::table('strategic_objectives', function (Blueprint $table) {
            $table->boolean('is_internal')->default(false)->after('scope');
        });

        DB::table('strategic_objectives')->where('scope', ActionScopeEnum::INTERNAL->value)->update(['is_internal' => true]);
        DB::table('strategic_objectives')->where('scope', ActionScopeEnum::EXTERNAL->value)->update(['is_internal' => false]);

        Schema::table('strategic_objectives', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
};
