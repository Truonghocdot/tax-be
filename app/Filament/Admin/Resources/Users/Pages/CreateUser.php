<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_active'] = true;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Get the created user data
        $user = $this->record;
        $password = $this->data['password'] ?? null;

        // Send a success notification with user credentials
        if ($password) {
            Notification::make()
                ->success()
                ->title('✅ Tạo tài khoản thành công!')
                ->body("
                    **Thông tin đăng nhập:**
                    
                    👤 **Tên đăng nhập:** {$user->username}
                    🔑 **Mật khẩu:** {$password}
                    📱 **Số điện thoại:** {$user->phone}
                    
                    ⚠️ Vui lòng lưu lại thông tin này để gửi cho khách hàng!
                ")
                ->persistent()
                ->duration(null) // Keep notification until manually closed
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        // Stay on the list page after creation
        return $this->getResource()::getUrl('index');
    }
}
