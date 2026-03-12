<?php

namespace App\Filament\Resources\EmploymentStatuses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class EmploymentStatusInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Section::make('Employment Status Details')
                    ->schema([
                TextEntry::make('name'),
        
           ]),
            ])->columns(1);
    }
}
