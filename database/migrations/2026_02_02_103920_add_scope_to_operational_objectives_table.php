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
        Schema::table('operational_objectives', function (Blueprint $table) {
            $table->string('scope')->after('department')->default(ActionScopeEnum::EXTERNAL->value);
        });

        DB::table('operational_objectives')
            ->where('department', 'COMMUN')
            ->update(['scope' => ActionScopeEnum::INTERNAL->value]);

        DB::table('operational_objectives')
            ->where('department', '!=', 'COMMUN')
            ->update(['scope' => ActionScopeEnum::EXTERNAL->value]);
    }
};
