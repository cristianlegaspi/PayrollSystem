<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

              Section::make('User Information Details')
                    ->schema([

                        TextEntry::make('name'),
                        TextEntry::make('branch.branch_name')
                           ->label('Branch Name'),
                     
                     
                        ])->columns(2),

               Section::make('User Credentials Details')
                    ->schema([

                      
                        TextEntry::make('email')
                           ->label('Email address'),

                        TextEntry::make('role.role_name')
                           ->label('Role Name'),
                        ])->columns(2),

            ])->columns(1);
    }
}
