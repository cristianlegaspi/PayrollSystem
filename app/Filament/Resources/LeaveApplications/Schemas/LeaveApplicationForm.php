<?php

namespace App\Filament\Resources\LeaveApplications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\LeaveBalance;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Carbon\Carbon;




class LeaveApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                Select::make('employee_id')
                ->relationship('employee', 'full_name')
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function ($state, Set $set) {

                    $balance = LeaveBalance::where('employee_id', $state)
                        ->where('year', now()->year)
                        ->first();

                    $set('remaining_leave', $balance?->remaining_credit ?? 5);
                })
                ->required(),

                TextInput::make('remaining_leave')
                    ->label('Remaining Leave Credits')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(5),
              DatePicker::make('from_date')
                    ->label('From')
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (Get $get, Set $set) {

                        if (!$get('to_date')) {
                            return;
                        }

                        $days = Carbon::parse($get('from_date'))
                            ->diffInDays(Carbon::parse($get('to_date'))) + 1;

                        $set('days', $days);

                    }),

                   DatePicker::make('to_date')
                    ->label('To')
                    ->live()
                    ->required()
                    ->minDate(fn (Get $get) => $get('from_date'))
                    ->afterStateUpdated(function (Get $get, Set $set) {

                        if (!$get('from_date')) {
                            return;
                        }

                        $days = Carbon::parse($get('from_date'))
                            ->diffInDays(Carbon::parse($get('to_date'))) + 1;

                        $set('days', $days);

                    }),

           TextInput::make('days')
                    ->disabled()
                    ->dehydrated()
                    ->rule(function (Get $get) {

                        return function ($attribute, $value, $fail) use ($get) {

                            $balance = \App\Models\LeaveBalance::where('employee_id', $get('employee_id'))
                                ->where('year', now()->year)
                                ->first();

                            $remaining = $balance?->remaining_credit ?? 5;

                            if ($value > $remaining) {
                                $fail("Only {$remaining} leave day(s) remaining.");
                            }

                        };

                    })  ->columnSpanFull(),

                Select::make('leave_type')
                    ->options([
                        'Vacation' => 'Vacation Leave',
                        'Sick' => 'Sick Leave',
                        'Emergency' => 'Emergency Leave',
                        'Personal' => 'Personal Leave',
                    ])
                    ->searchable()
                      ->columnSpanFull()
                    ->required(),

                Textarea::make('reason')
                    ->rows(4)
                    ->columnSpanFull(),

                Hidden::make('status')
                    ->default('Pending'),

            ]);
    }
}