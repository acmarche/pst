<?php

declare(strict_types=1);

use App\Enums\ActionSynergyEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('strategic_objectives', function (Blueprint $table) {
            $table->enum('synergy', ActionSynergyEnum::toArray())->nullable(false)->default(ActionSynergyEnum::NO)->change();
        });

        Schema::table('operational_objectives', function (Blueprint $table) {
            $table->enum('synergy', ActionSynergyEnum::toArray())->nullable(false)->default(ActionSynergyEnum::NO)->change();
        });
        Schema::table('actions', function (Blueprint $table) {
            $table->enum('synergy', ActionSynergyEnum::toArray())->nullable(false)->default(ActionSynergyEnum::NO)->change();
        });
    }

    public function down(): void {}
};
