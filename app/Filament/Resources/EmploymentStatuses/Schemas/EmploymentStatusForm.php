<?php

namespace App\Filament\Resources\EmploymentStatuses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class EmploymentStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([


              Section::make('Employment Status Details')
                    ->schema([
              
                TextInput::make('name')
                    ->unique()
                    ->required(),
            ]),

            ])->columns(1);
    }
}
