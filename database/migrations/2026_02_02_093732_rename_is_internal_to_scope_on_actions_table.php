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
        Schema::table('actions', function (Blueprint $table) {
            $table->string('scope')->default(ActionScopeEnum::INTERNAL->value)->after('is_internal');
        });

        DB::table('actions')->where('is_internal', true)->update(['scope' => ActionScopeEnum::INTERNAL->value]);
        DB::table('actions')->where('is_internal', false)->update(['scope' => ActionScopeEnum::EXTERNAL->value]);

        Schema::table('actions', function (Blueprint $table) {
            $table->dropColumn('is_internal');
        });
    }

    public function down(): void
    {
        Schema::table('actions', function (Blueprint $table) {
            $table->boolean('is_internal')->default(true)->after('scope');
        });

        DB::table('actions')->where('scope', ActionScopeEnum::INTERNAL->value)->update(['is_internal' => true]);
        DB::table('actions')->where('scope', ActionScopeEnum::EXTERNAL->value)->update(['is_internal' => false]);

        Schema::table('actions', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
};
