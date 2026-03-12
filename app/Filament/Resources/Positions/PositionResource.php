<?php

namespace App\Filament\Resources\Positions;

use App\Filament\Resources\Positions\Pages\CreatePosition;
use App\Filament\Resources\Positions\Pages\EditPosition;
use App\Filament\Resources\Positions\Pages\ListPositions;
use App\Filament\Resources\Positions\Pages\ViewPosition;
use App\Filament\Resources\Positions\Schemas\PositionForm;
use App\Filament\Resources\Positions\Schemas\PositionInfolist;
use App\Filament\Resources\Positions\Tables\PositionsTable;
use App\Models\Position;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $recordTitleAttribute = 'Position';

     protected static string | UnitEnum | null $navigationGroup = 'Settings Management';

     protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PositionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PositionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PositionsTable::configure($table);
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
            'index' => ListPositions::route('/'),
            'create' => CreatePosition::route('/create'),
            'view' => ViewPosition::route('/{record}'),
            'edit' => EditPosition::route('/{record}/edit'),
        ];
    }
}
