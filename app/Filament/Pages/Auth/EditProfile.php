<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Repository\FilamentColorRepository;
use BackedEnum;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

final class EditProfile extends BaseEditProfile
{
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-document-text';

    public function getTitle(): string|Htmlable
    {
        return $this->getUser()->first_name.' '.$this->getUser()->first_name;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                $this->getEmailFormComponent(),
                Select::make('color_primary')
                    ->options(FilamentColorRepository::colors())
                    ->label('Couleur principale'),
                Select::make('color_secondary')
                    ->options(FilamentColorRepository::colors())
                    ->allowHtml()
                    ->label('Couleur secondaire'),
            ])
            ->columns(2);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/edit-profile.form.email.label'))
            ->email()
            ->readOnly()
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true)
            ->columnSpanFull();
    }

    protected function afterSave(): void
    {
        $this->redirect(request()->header('referer'));
    }
}
