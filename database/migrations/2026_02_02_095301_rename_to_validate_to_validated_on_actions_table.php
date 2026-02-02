<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('actions', function (Blueprint $table) {
            $table->boolean('validated')->default(false)->after('to_validate');
        });

        // Invert the logic: to_validate=true means not validated, to_validate=false means validated
        DB::table('actions')->where('to_validate', true)->update(['validated' => false]);
        DB::table('actions')->where('to_validate', false)->update(['validated' => true]);

        Schema::table('actions', function (Blueprint $table) {
            $table->dropColumn('to_validate');
        });
    }

    public function down(): void
    {
        Schema::table('actions', function (Blueprint $table) {
            $table->boolean('to_validate')->default(true)->after('validated');
        });

        // Invert back: validated=true means to_validate=false, validated=false means to_validate=true
        DB::table('actions')->where('validated', true)->update(['to_validate' => false]);
        DB::table('actions')->where('validated', false)->update(['to_validate' => true]);

        Schema::table('actions', function (Blueprint $table) {
            $table->dropColumn('validated');
        });
    }
};
