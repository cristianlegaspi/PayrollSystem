<?php

namespace App\Filament\Resources\Positions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
class PositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

            Section::make('Position Details')
                    ->schema([
                TextInput::make('position_name')
                    ->required(),
              ]),
            ])->columns(1);
    }
}
