<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('User Information Details')
                    ->schema([

                        TextInput::make('name')
                        ->unique()
                            ->required(),

                        Select::make('branch_id')
                            ->label('Branch')
                            ->relationship('branch', 'branch_name')
                            ->nullable(),

                    ])->columns(2),

                Section::make('User Credentials')
                    ->schema([

                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->unique()
                            ->required(),
                        TextInput::make('password')
                            ->password()
                            ->required(),

                        Select::make('role_id')
                            ->label('Role')
                            ->relationship('role', 'role_name')
                            ->nullable(),

                    ])->columns(3),
            ])->columns(1);
    }
}
