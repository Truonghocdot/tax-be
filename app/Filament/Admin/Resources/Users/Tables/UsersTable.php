<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use App\Models\Bank;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên'),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('phone')->label('Số điện thoại'),
                TextColumn::make('username')->label('Tên đăng nhập'),
                TextColumn::make('role')->label('Vai trò')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => 'Admin',
                        2 => 'Client',
                    }),
                TextColumn::make('is_active')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Đang hoạt động' : 'Chờ duyệt')
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('Duyệt tài khoản')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Duyệt tài khoản')
                    ->modalDescription(fn (User $record): string => "Cho phép {$record->username} đăng nhập vào hệ thống?")
                    ->visible(fn (User $record): bool => ! $record->is_active && ! $record->isAdmin())
                    ->action(function (User $record): void {
                        $record->update(['is_active' => true]);

                        Notification::make()
                            ->title('Tài khoản đã được duyệt')
                            ->success()
                            ->send();
                    }),
                Action::make('update_qr')
                    ->label('Cập nhật QR Bank')
                    ->icon('heroicon-o-qr-code')
                    ->modalHeading('Cập nhật thông tin QR Bank')
                    ->fillForm(function (User $record): array {
                        return [
                            'bin_bank' => $record->qrBank?->bin_bank,
                            'number_account' => $record->qrBank?->number_account,
                            'amount' => $record->qrBank?->amount,
                            'account_name' => $record->qrBank?->account_name,
                            'description' => $record->qrBank?->description,
                            'tax_id' => $record->qrBank?->tax_id,
                            'company_name' => $record->qrBank?->company_name,
                        ];
                    })
                    ->schema([
                        Select::make('bin_bank')
                            ->label('Bin Bank')
                            ->searchable()
                            ->options(fn () => Bank::all()->pluck('short_name', 'bin'))
                            ->required()
                            ->validationMessages([
                                'required' => 'Bank không được để trống',
                            ]),
                        TextInput::make('number_account')
                            ->label('Số tài khoản')
                            ->required()
                            ->validationMessages([
                                'required' => 'Số tài khoản không được để trống',
                            ]),
                        TextInput::make('amount')
                            ->label('Số tiền')
                            ->numeric()
                            ->validationMessages([
                                'numeric' => 'Số tiền phải là số',
                            ]),
                        TextInput::make('account_name')
                            ->label('Tên chủ tài khoản'),
                        TextInput::make('description')
                            ->label('Mô tả'),
                        TextInput::make('tax_id')
                            ->label('Mã số thuế'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->qrBank()->updateOrCreate(
                            ['user_id' => $record->id],
                            $data
                        );

                        Notification::make()
                            ->title('Đã cập nhật QR Bank thành công')
                            ->success()
                            ->send();
                    }),
            ])
            ->poll('2s')
            ->defaultSort('created_at', 'desc')
            ->checkIfRecordIsSelectableUsing(fn (User $record): bool => $record->canBeDeleted())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
