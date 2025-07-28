<?php

declare(strict_types=1);

namespace App\Actions;

use Filament\Actions\Action;

final class PreviousAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->hiddenLabel()
            ->icon('heroicon-o-arrow-left')
            ->outlined()
            ->tooltip("Previous {$this->getRecordTitle()}");
    }

    public static function getDefaultName(): ?string
    {
        return 'previous';
    }
}
