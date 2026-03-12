<?php

namespace App\Filament\Resources\PayrollPeriods\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;


class PayrollPeriodInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Period Description Details')
                    ->schema([

                        TextEntry::make('description'),
                        TextEntry::make('status')
                            ->badge()
                            ->colors([
                                'danger' => 'Closed',
                                'success' => 'Open',
                                'primary' => 'Finalized',
                            ])


                    ])->columns(1),

                Section::make('Covered Date Details')
                    ->schema([

                        TextEntry::make('start_date')
                            ->label('Start Date')
                            ->date(),
                        TextEntry::make('end_date')
                            ->label('End Date')
                            ->date(),

                    ])->columns(2),




            ]);
    }
}
