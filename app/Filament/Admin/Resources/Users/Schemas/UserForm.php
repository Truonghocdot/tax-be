<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Thông tin cá nhân')
                    ->description('Thông tin cơ bản của người dùng')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('phone')
                            ->label('Số điện thoại')
                            ->placeholder('0xxxxxxxxx')
                            ->tel()
                            ->required()
                            ->maxLength(15)
                            ->regex('/^([0-9\s\-\+\(\)]*)$/')
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, string $context) {
                                // Auto-generate username from phone when creating
                                if ($context === 'create' && filled($state)) {
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $state);
                                    $set('username', 'user_' . $cleanPhone);
                                }
                            })
                            ->helperText('Số điện thoại liên hệ - Sẽ tự động tạo username'),

                        TextInput::make('name')
                            ->label('Họ và tên')
                            ->placeholder('Nhập họ và tên đầy đủ')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Tên đăng nhập hệ thống, phải là duy nhất'),

                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('example@domain.com')
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Email (không bắt buộc)'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Thông tin đăng nhập')
                    ->description('Thông tin xác thực - Có thể tự động tạo')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        TextInput::make('username')
                            ->label('Tên đăng nhập')
                            ->placeholder('Tự động tạo từ SĐT hoặc nhập thủ công')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->suffixAction(
                                Action::make('generateUsername')
                                    ->icon('heroicon-o-sparkles')
                                    ->tooltip('Tạo username ngẫu nhiên')
                                    ->visible(fn(string $context) => $context === 'create')
                                    ->action(function (Set $set, Get $get) {
                                        $phone = $get('phone');
                                        if (filled($phone)) {
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                                            $set('username', 'user_' . $cleanPhone);
                                        } else {
                                            $set('username', 'user_' . strtolower(Str::random(8)));
                                        }
                                    })
                            )
                            ->helperText('Tên đăng nhập (tự động tạo từ SĐT hoặc click ✨)'),

                        TextInput::make('password')
                            ->label('Mật khẩu')
                            ->placeholder('Nhập hoặc tạo tự động')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            ->suffixAction(
                                Action::make('generatePassword')
                                    ->icon('heroicon-o-key')
                                    ->tooltip('Tạo mật khẩu ngẫu nhiên')
                                    ->visible(fn(string $context) => $context === 'create')
                                    ->action(function (Set $set) {
                                        // Generate a secure random password: 2 uppercase + 2 lowercase + 2 numbers + 2 special
                                        $password =
                                            strtoupper(Str::random(2)) .
                                            strtolower(Str::random(2)) .
                                            rand(10, 99) .
                                            Str::random(2) .
                                            '@#';
                                        $set('password', $password);
                                    })
                            )
                            ->helperText(
                                fn(string $context): string =>
                                $context === 'create'
                                    ? '🔑 Click biểu tượng chìa khóa để tạo mật khẩu ngẫu nhiên (8+ ký tự)'
                                    : 'Để trống nếu không muốn thay đổi mật khẩu'
                            ),

                        Select::make('role')
                            ->label('Vai trò')
                            ->options([
                                2 => '👤 Khách hàng',
                            ])
                            ->default(2)
                            ->required()
                            ->disabled()
                            ->helperText('Mặc định: Khách hàng'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Thông tin định danh')
                    ->description('Hình ảnh xác thực danh tính (CCCD/CMND) - Không bắt buộc khi tạo')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        FileUpload::make('front_cccd')
                            ->label('Mặt trước CCCD/CMND')
                            ->image()
                            ->disk('public')
                            ->directory('identity_verification')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->helperText('Tải lên ảnh mặt trước CCCD/CMND (tối đa 5MB)'),

                        FileUpload::make('back_cccd')
                            ->label('Mặt sau CCCD/CMND')
                            ->image()
                            ->disk('public')
                            ->directory('identity_verification')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->helperText('Tải lên ảnh mặt sau CCCD/CMND (tối đa 5MB)'),

                        FileUpload::make('holding_cccd')
                            ->label('Ảnh cầm CCCD/CMND')
                            ->image()
                            ->disk('public')
                            ->directory('identity_verification')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->helperText('Tải lên ảnh chân dung cầm CCCD/CMND (tối đa 5MB)'),
                    ])
                    ->columns(3)
                    ->collapsed()
                    ->collapsible(),
            ]);
    }
}
