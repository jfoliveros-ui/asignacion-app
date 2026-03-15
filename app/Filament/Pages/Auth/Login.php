<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    public function getHeading(): string | Htmlable | null
    {
        return '';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return null;
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Usuario')
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Demasiados intentos')
                ->body("Intenta nuevamente en {$exception->secondsUntilAvailable} segundos.")
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        $credentials = [
            'username' => $data['username'] ?? null,
            'password' => $data['password'] ?? null,
        ];

        if (! Filament::auth()->attempt($credentials, $data['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'data.username' => 'Las credenciales son incorrectas.',
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}