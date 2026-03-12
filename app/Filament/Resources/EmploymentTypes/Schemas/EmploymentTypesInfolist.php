<?php

namespace App\Filament\Resources\EmploymentTypes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class EmploymentTypesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                 Section::make('Employment Types Details')
                    ->schema([
                TextEntry::make('name'),
        
           ]),
            ])->columns(1);
    }
}
