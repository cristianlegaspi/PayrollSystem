<?php

namespace App\Filament\Resources\EmploymentTypes;

use App\Filament\Resources\EmploymentTypes\Pages\CreateEmploymentTypes;
use App\Filament\Resources\EmploymentTypes\Pages\EditEmploymentTypes;
use App\Filament\Resources\EmploymentTypes\Pages\ListEmploymentTypes;
use App\Filament\Resources\EmploymentTypes\Pages\ViewEmploymentTypes;
use App\Filament\Resources\EmploymentTypes\Schemas\EmploymentTypesForm;
use App\Filament\Resources\EmploymentTypes\Schemas\EmploymentTypesInfolist;
use App\Filament\Resources\EmploymentTypes\Tables\EmploymentTypesTable;
use App\Models\EmploymentTypes;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmploymentTypesResource extends Resource
{
    protected static ?string $model = EmploymentTypes::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowUpOnSquare;

    protected static ?string $recordTitleAttribute = 'EmploymentTypes';

    protected static string | UnitEnum | null $navigationGroup = 'Settings Management';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return EmploymentTypesForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmploymentTypesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmploymentTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmploymentTypes::route('/'),
            'create' => CreateEmploymentTypes::route('/create'),
            'view' => ViewEmploymentTypes::route('/{record}'),
            'edit' => EditEmploymentTypes::route('/{record}/edit'),
        ];
    }
}
