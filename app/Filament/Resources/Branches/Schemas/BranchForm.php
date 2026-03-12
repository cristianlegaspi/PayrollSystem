<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

              Section::make('Branch Details')
                    ->schema([
                TextInput::make('branch_name')
                    ->required(),
            ]),

             ])->columns(1);
    }
}
