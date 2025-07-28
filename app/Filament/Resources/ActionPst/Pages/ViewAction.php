<?php

namespace App\Filament\Resources\ActionPst\Pages;

use App\Actions\CanPaginateViewRecordTrait;
use App\Actions\ReminderAction;
use App\Filament\Resources\ActionPst\Schemas\ActionInfolist;
use App\Filament\Resources\ActionPstResource;
use App\Filament\Resources\OperationalObjectiveResource;
use App\Filament\Resources\StrategicObjectiveResource;
use App\Models\Action as ActionModel;
use Filament\Actions;
use Filament\Actions\Action as ActionAction;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;

class ViewAction extends ViewRecord
{
    use CanPaginateViewRecordTrait;

    protected static string $resource = ActionPstResource::class;

    public function getTitle(): string
    {
        return $this->record->name ?? 'Empty name';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            //  PreviousAction::make(),
            //  NextAction::make(),
            ActionGroup::make([
                    ActionAction::make('rapport')
                        ->label('Export en pdf')
                        ->icon('tabler-pdf')
                        ->url(fn(ActionModel $record) => route('download.action', $record))
                        ->action(function () {
                            Notification::make()
                                ->title('Pdf exporté')
                                ->success()
                                ->send();
                        }),
                    ReminderAction::createAction($this->record),
                    Actions\DeleteAction::make()
                        ->icon('tabler-trash'),
                ]
            )
                ->label('Autres actions')
                ->button()
                ->size(Size::Large)
                ->color('secondary'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        $oo = $this->record->operationalObjective()->first();
        $os = $oo->strategicObjective()->first();

        return [
            StrategicObjectiveResource::getUrl('index') => 'Objectifs Stratégiques',
            StrategicObjectiveResource::getUrl('view', ['record' => $os]) => $os->name,
            OperationalObjectiveResource::getUrl('view', ['record' => $oo]) => $oo->name,
            'Action',
            //$this->getBreadcrumb(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return ActionInfolist::infolist($schema);
    }
}
