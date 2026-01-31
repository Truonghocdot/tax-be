<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Thông tin cá nhân')
                    ->schema([
                        TextInput::make('name')
                            ->label('Họ tên')
                            ->required(),
                        TextInput::make('phone')
                            ->label('Số điện thoại')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email(),
                    ])->columns(2),

                Section::make('Thông tin đăng nhập')
                    ->schema([
                        TextInput::make('username')
                            ->label('Tên đăng nhập')
                            ->required(),
                        TextInput::make('password')
                            ->label('Mật khẩu')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create'),
                        TextInput::make('role')
                            ->label('Vai trò')
                            ->default('user'),
                    ])->columns(2),

                Section::make('Thông tin định danh')
                    ->schema([
                        FileUpload::make('front_cccd')
                            ->label('Mặt trước CCCD')
                            ->image()
                            ->disk('public')
                            ->directory('identity_verification')
                            ->openable()
                            ->downloadable(),
                        FileUpload::make('back_cccd')
                            ->label('Mặt sau CCCD')
                            ->image()
                            ->disk('public')
                            ->directory('identity_verification')
                            ->openable()
                            ->downloadable(),
                        FileUpload::make('holding_cccd')
                            ->label('Ảnh cầm CCCD')
                            ->image()
                            ->disk('public')
                            ->directory('identity_verification')
                            ->openable()
                            ->downloadable(),
                    ])->columns(2),
            ]);
    }
}
