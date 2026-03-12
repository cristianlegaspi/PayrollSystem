<?php

namespace App\Filament\Resources\Positions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class PositionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

             Section::make('User Details')
                    ->schema([
                TextEntry::make('position_name'),
               ])->columns(2),
               
            ]);
    }
}
