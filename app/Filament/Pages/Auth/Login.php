<?php

namespace App\Filament\Pages\Auth;

use App\Auth\LdapAuthService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BasePage;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;

final class Login extends BasePage
{
    public function getHeading(): string|Htmlable|null
    {
        return 'Programme Stratégique Transversal';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Courage 🐤';
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! $user = LdapAuthService::checkPassword($data['email'], $data['password'])) {
            $this->throwFailureValidationException();
        } else {
            Filament::auth()->login($user, true);
            $user = Filament::auth()->user();
        }

        /*
        if (!Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();*/

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    public function mount(): void
    {
        parent::mount();

        if (app()->isLocal()) {
            $this->form->fill([
                'email' => config('app.default_user.username'),
                'password' => config('app.default_user.password'),
                'remember' => true,
            ]);
        }
    }

    /**
     * remove type email
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Nom d\'utilisateur')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }
}
