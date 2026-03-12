<?php

namespace App\Filament\Resources\EmploymentTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class EmploymentTypesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
              Section::make('Employment Types Details')
                    ->schema([
              
                TextInput::make('name')
                    ->required(),
            ]),

            ])->columns(1);
    }
}
