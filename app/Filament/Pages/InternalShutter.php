<?php

namespace App\Filament\Pages;

use App\Constant\DepartmentEnum;
use App\Models\StrategicObjective;
use App\Repository\StrategicObjectiveRepository;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;

class InternalShutter extends Page
{
    protected static ?int $navigationSort = 5;

    /**
     * @var Collection|StrategicObjective[] $oss
     */
    public Collection|array $oss = [];

    public static function getNavigationLabel(): string
    {
        return 'Volet interne';
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return 'tabler-list';
    }

    protected  string $view = 'filament.resources.strategic-objective-list';

    public function getTitle(): string|Htmlable
    {
        return ' Volet interne';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Actions communes Ville/Cpas';
    }

    public function __construct()
    {
        $this->oss = StrategicObjectiveRepository::findByDepartmentWithOosAndActions(DepartmentEnum::COMMON->value);
    }
}
