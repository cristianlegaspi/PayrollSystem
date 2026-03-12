<?php

namespace App\Filament\Resources\PayrollPeriods\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;

class PayrollPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
              Section::make('Period Description Details')
                    ->schema([

                       TextInput::make('description')
                        ->label('Period Description')
                        ->placeholder('January 1-15, 2026')
                        ->required(),

                         Select::make('status')
                        ->options(['closed' => 'Closed', 'open' => 'Open', 'finalized' => 'Finalized'])
                        ->default('Closed')
                        ->required(),
         


                     ])->columns(2),

                
             Section::make('Covered Date Details')
                    ->schema([

                     
                    DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required(),


                     ])->columns(2),


            
            
             
                
                
                
                
             
            ])->columns(1);
    }
}
