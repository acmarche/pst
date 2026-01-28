<?php

declare(strict_types=1);

use App\Enums\ActionRoadmapEnum;
use App\Enums\ActionStateEnum;
use App\Enums\ActionSynergyEnum;
use App\Enums\ActionTypeEnum;
use App\Enums\DepartmentEnum;
use App\Models\Action;
use App\Models\Odd;
use App\Models\OperationalObjective;
use App\Models\Partner;
use App\Models\Service;
use App\Models\StrategicObjective;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_objectives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('department', DepartmentEnum::toArray())
                ->nullable(false);
            $table->integer('position')->default(0);
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });

        Schema::create('operational_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StrategicObjective::class)->constrained('strategic_objectives')->cascadeOnDelete();
            $table->string('name');
            $table->enum('department', DepartmentEnum::toArray())
                ->nullable(false);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(OperationalObjective::class)
                ->constrained('operational_objectives')
                ->cascadeOnDelete();
            $table->string('name');
            $table->enum('department', DepartmentEnum::toArray())
                ->nullable(false);
            $table->text('description')->nullable();
            $table->text('note')->nullable();
            $table->date('due_date')->nullable();
            $table->text('budget_estimate')->nullable();
            $table->text('financing_mode')->nullable();
            $table->enum('state', ActionStateEnum::toArray())->nullable()->default(null);
            $table->boolean('to_validate')->default(true);
            $table->enum('type', ActionTypeEnum::toArray())->nullable();
            $table->enum('roadmap', ActionRoadmapEnum::toArray())->nullable();
            $table->enum('synergy', ActionSynergyEnum::toArray())->nullable();
            $table->integer('state_percentage')->nullable();
            $table->text('work_plan')->nullable();
            $table->text('evaluation_indicator')->nullable();
            $table->string('user_add');
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('initials')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('initials')->nullable();
            $table->timestamps();
        });

        Schema::create('odds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('image_name')->nullable();
            $table->text('description')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Action::class)->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->integer('pourcent')->nullable();
            $table->string('user_add');
            $table->timestamps();
        });

        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Action::class)->constrained()->cascadeOnDelete();
            $table->string('property')->nullable();
            $table->text('body')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('user_add');
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Action::class)->constrained()->cascadeOnDelete();
            $table->uuid()->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->nullableTimestamps();
        });

        Schema::create('action_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Action::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->unique(['action_id', 'user_id']);
        });

        Schema::create('action_mandatory', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Action::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->unique(['action_id', 'user_id']);
        });

        Schema::create('action_partner', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Action::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Partner::class)->constrained()->cascadeOnDelete();
            $table->unique(['action_id', 'partner_id']);
        });

        Schema::create('action_service_leader', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Action::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Service::class)->constrained()->cascadeOnDelete();
            $table->unique(['action_id', 'service_id']);
        });

        Schema::create('action_service_partner', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Action::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Service::class)->constrained()->cascadeOnDelete();
            $table->unique(['action_id', 'service_id']);
        });

        Schema::create('action_related', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Action::class, 'action_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Action::class, 'related_action_id')->constrained()->cascadeOnDelete();
            $table->unique(['action_id', 'related_action_id']);
        });

        Schema::create('action_odd', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Action::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Odd::class)->constrained()->cascadeOnDelete();
            $table->unique(['action_id', 'odd_id']);
        });

        Schema::create('service_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Service::class)->constrained()->cascadeOnDelete();
            $table->unique(['user_id', 'service_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('odds');
        Schema::dropIfExists('services');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('actions');
        Schema::dropIfExists('followups');
        Schema::dropIfExists('action_service_leader');
        Schema::dropIfExists('action_service_partner');
        Schema::dropIfExists('action_user');
        Schema::dropIfExists('action_mandatory');
        Schema::dropIfExists('action_partner');
        Schema::dropIfExists('action_odd');
        Schema::dropIfExists('service_user');
        Schema::dropIfExists('operational_objectives');
        Schema::dropIfExists('strategic_objectives');
    }
};
