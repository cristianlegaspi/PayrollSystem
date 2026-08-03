<?php

namespace App\Filament\Resources\LeaveApplications\Tables;

use App\Models\LeaveBalance;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('from_date', 'desc')

            ->columns([

                TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('from_date')
                    ->label('From')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('to_date')
                    ->label('To')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('leave_type')
                    ->label('Leave Type')
                    ->badge(),

                TextColumn::make('days')
                    ->label('Days')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->reason),

                TextColumn::make('approved_date')
                    ->label('Approved Date')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Date Filed')
                    ->dateTime('M d, Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->filters([

            ])

            ->recordActions([

                ViewAction::make(),

                EditAction::make()
                    ->visible(fn ($record) => $record->status === 'Pending'),

                /*
                |--------------------------------------------------------------------------
                | APPROVE LEAVE
                |--------------------------------------------------------------------------
                */
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) =>
                        $record->status === 'Pending'
                        && Auth::check()
                        && in_array(
                            optional(Auth::user()->role)->role_name,
                            ['Owner', 'Super Admin']
                        )
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {

                        DB::transaction(function () use ($record) {

                            $balance = LeaveBalance::firstOrCreate(
                                [
                                    'employee_id' => $record->employee_id,
                                    'year' => now()->year,
                                ],
                                [
                                    'annual_credit' => 5,
                                    'used_credit' => 0,
                                    'remaining_credit' => 5,
                                ]
                            );

                            if ($balance->remaining_credit < $record->days) {

                                Notification::make()
                                    ->title('Insufficient Leave Credits')
                                    ->body("Remaining Credits: {$balance->remaining_credit}")
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $balance->update([
                                'used_credit' => $balance->used_credit + $record->days,
                                'remaining_credit' => $balance->remaining_credit - $record->days,
                            ]);

                            $record->update([
                                'status' => 'Approved',
                                'approved_date' => now(),
                            ]);
                        });

                        Notification::make()
                            ->title('Leave application approved.')
                            ->success()
                            ->send();
                    }),

                /*
                |--------------------------------------------------------------------------
                | PRINT LEAVE
                |--------------------------------------------------------------------------
                */
                Action::make('printLeave')
                    ->label('Print Leave')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'Approved')
                    ->url(fn ($record) => route('leave.print', [
                        'leaveApplication' => $record->id,
                    ]))
                    ->openUrlInNewTab(),

                /*
                |--------------------------------------------------------------------------
                | REJECT LEAVE
                |--------------------------------------------------------------------------
                */
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) =>
                        $record->status === 'Pending'
                        && Auth::check()
                        && in_array(
                            optional(Auth::user()->role)->role_name,
                            ['Owner', 'Super Admin']
                        )
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {

                        $record->update([
                            'status' => 'Rejected',
                        ]);

                        Notification::make()
                            ->title('Leave application rejected.')
                            ->danger()
                            ->send();
                    }),

            ])

            ->toolbarActions([

                BulkActionGroup::make([

                    DeleteBulkAction::make(),

                ]),

            ]);
    }
}