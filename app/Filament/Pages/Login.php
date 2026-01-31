<?php

namespace App\Filament\Pages;

use App\Constants\UserRole;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function boot() {}

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')
                ->label('Email')
                ->string()
                ->required()
                ->autocomplete('email')
                ->extraInputAttributes(['tabindex' => 1])
                ->validationMessages([
                    'required' => 'Không được để trống',
                ]),
            TextInput::make('password')
                ->label('Mật khẩu')
                ->password()
                ->revealable()
                ->autocomplete('current-password')
                ->required()
                ->extraInputAttributes(['tabindex' => 2])
                ->validationMessages([
                    'required' => 'Không được để trống',
                ]),
        ]);
    }


    /**
     * @throws ValidationException
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::auth/pages/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => $exception->minutesUntilAvailable,
                ]))
                ->body(array_key_exists('body', __('filament-panels::auth/pages/login.notifications.throttled') ?: []) ? __('filament-panels::auth/pages/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => $exception->minutesUntilAvailable,
                ]) : null)
                ->danger()
                ->send();
        }
        $data = $this->form->getState();


        $credentials = [
            'email'          => $data['email'],
            'password'       => $data['password'],
        ];

        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            Notification::make()
                ->title('Tài khoản không tồn tại')
                ->danger()
                ->send();

            return null;
        }
        if (!Hash::check($credentials['password'], $user->password)) {
            Notification::make()
                ->title('Mật khẩu không chính xác')
                ->danger()
                ->send();

            return null;
        }
        if ($user->role != UserRole::ADMIN->value) {
            Notification::make()
                ->title('Bạn không có quyền truy cập')
                ->danger()
                ->send();

            return null;
        }

        filament()->auth()->login($user);

        return app(LoginResponse::class);
    }

    public function getView(): string
    {
        return 'filament.pages.login';
    }
}
