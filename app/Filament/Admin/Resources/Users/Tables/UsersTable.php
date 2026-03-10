<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use App\Models\Bank;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

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
                    ->formatStateUsing(fn($state) => match ($state) {
                        1 => 'Admin',
                        2 => 'Client',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
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
                            ->options(fn() => Bank::all()->pluck('name', 'bin'))
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
                        TextInput::make('company_name')
                            ->label('Tên công ty'),
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
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
