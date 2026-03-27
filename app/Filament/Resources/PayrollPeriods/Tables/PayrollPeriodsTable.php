<?php

namespace App\Filament\Resources\PayrollPeriods\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Auth;



use Filament\Notifications\Notification;

class PayrollPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Payroll Period')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payrolls_count')
                    ->label('Total Employees')
                    ->counts('payrolls')
                    ->sortable(),

                TextColumn::make('total_gross')
                    ->label('Total Gross Payout')
                    ->money('PHP')
                    ->getStateUsing(
                        fn($record) =>
                        $record->payrolls->sum('gross_pay')
                    ),

                TextColumn::make('total_net')
                    ->label('Total Net Payout')
                    ->money('PHP')
                    ->getStateUsing(
                        fn($record) =>
                        $record->payrolls->sum('net_pay')
                    ),

                TextColumn::make('status')
                    ->label('Payroll Status')
                    ->badge()
                    ->colors([
                        'danger' => 'Closed',
                        'warning' => 'Open',
                        'success' => 'Finalized',
                    ]),

                TextColumn::make('remarks')
                    ->label('Payroll Remarks')
                    ->badge()
                    ->colors([
                        'success' => 'Approved',
                        'warning' => 'Pending',
                        'danger' => 'Rejected',
                    ]),
            ])

            ->recordActions([

                // ViewAction::make(),
                EditAction::make(),

                // REVIEW PDF (only when Finalized AND NOT Approved)
                Action::make('reviewPayrollReport')
                    ->label('Review Payroll')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(
                        fn($record) =>
                        $record->status === 'Finalized' &&
                            $record->remarks !== 'Approved' && 
                            Auth::check() &&
                            optional(Auth::user()->role)->role_name === 'Owner' // 👈 role restriction
                    )
                    ->url(fn($record) => route('payroll.print', ['period' => $record->id]))
                    ->openUrlInNewTab(),

                // PRINT (ONLY when Approved)
                Action::make('printPayrollReport')
                    ->label('Print Approved Payroll')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(
                        fn($record) =>
                        $record->remarks === 'Approved'
                    )
                    ->url(fn($record) => route('payroll.print', ['period' => $record->id]))
                    ->openUrlInNewTab(),

                // APPROVE (only when Finalized AND NOT Approved) AND ROLE = Owner
                Action::make('approvePayroll')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(
                        fn($record) =>
                        $record->status === 'Finalized' &&
                            $record->remarks !== 'Approved' &&
                            Auth::check() &&
                            optional(Auth::user()->role)->role_name === 'Owner' // 👈 role restriction
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['remarks' => 'Approved']);

                        Notification::make()
                            ->title('Payroll Approved')
                            ->success()
                            ->send();
                    }),

                // REJECT (only when Finalized AND NOT Approved) AND ROLE = Owner
                Action::make('rejectPayroll')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(
                        fn($record) =>
                        $record->status === 'Finalized' &&
                            $record->remarks !== 'Approved' &&
                            Auth::check() &&
                            optional(Auth::user()->role)->role_name === 'Owner' // 👈 role restriction
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Reject Payroll')
                    ->modalDescription('Are you sure you want to reject this payroll period?')
                    ->action(function ($record) {
                        $record->update([
                            'remarks' => 'Rejected',
                            'status' => 'Open', // reopen
                        ]);

                        Notification::make()
                            ->title('Payroll Rejected and Reopened')
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
