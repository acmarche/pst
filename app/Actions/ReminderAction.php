<?php

declare(strict_types=1);

namespace App\Actions;

use App\Filament\Resources\ActionPst\Schemas\ActionForm;
use App\Mail\ActionReminderMail;
use App\Models\Action as ActionModel;
use App\Models\User;
use Exception;
use Filament\Actions\Action as ActionAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

final class ReminderAction
{
    public static function createAction(Model|ActionModel $action): ActionAction
    {
        $defaultRecipients = $action->users()->pluck('users.id')->toArray();

        return ActionAction::make('reminder')
            ->label('Houspiller')
            ->icon('tabler-school-bell')
            ->modal()
            ->modalDescription('Envoyer un mail aux agents')
            ->modalHeading('Où en sommes-nous actuellement ?')
            ->modalContentFooter(new HtmlString('Un lien vers l\'action sera automatiquement ajouté'))
            ->schema(
                ActionForm::fieldsReminder()
            )
            ->fillForm([
                'recipients' => $defaultRecipients,
            ])
            ->action(function (array $data, ActionModel $action) {
                $emails = User::query()
                    ->whereIn('id', $data['recipients'])
                    ->pluck('email')
                    ->unique()
                    ->values();

                if ($emails->isEmpty()) {
                    $emails = collect(['jf@marche.be']);
                }

                try {
                    Mail::to($emails)
                        ->send(new ActionReminderMail($action, $data));
                } catch (Exception $e) {
                    dd($e->getMessage());
                }
            });
    }
}
