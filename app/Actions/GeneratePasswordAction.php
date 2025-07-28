<?php

declare(strict_types=1);

namespace App\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

final class GeneratePasswordAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->icon('heroicon-s-key')
            ->color('info')
            ->action(function (Set $set) {
                $password = Str::password();

                $set('password', $password);
                $set('passwordConfirmation', $password);

                Notification::make()
                    ->success()
                    ->title(__('Password successfully generated.'))
                    ->send();
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'generatePassword';
    }
}
