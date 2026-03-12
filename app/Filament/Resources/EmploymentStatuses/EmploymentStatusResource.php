<?php

namespace App\Filament\Resources\EmploymentStatuses;

use App\Filament\Resources\EmploymentStatuses\Pages\CreateEmploymentStatus;
use App\Filament\Resources\EmploymentStatuses\Pages\EditEmploymentStatus;
use App\Filament\Resources\EmploymentStatuses\Pages\ListEmploymentStatuses;
use App\Filament\Resources\EmploymentStatuses\Pages\ViewEmploymentStatus;
use App\Filament\Resources\EmploymentStatuses\Schemas\EmploymentStatusForm;
use App\Filament\Resources\EmploymentStatuses\Schemas\EmploymentStatusInfolist;
use App\Filament\Resources\EmploymentStatuses\Tables\EmploymentStatusesTable;
use App\Models\EmploymentStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;


class EmploymentStatusResource extends Resource
{
    protected static ?string $model = EmploymentStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Briefcase;

    protected static ?string $recordTitleAttribute = 'EmploymentStatus';

     protected static string | UnitEnum | null $navigationGroup = 'Settings Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return EmploymentStatusForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmploymentStatusInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmploymentStatusesTable::configure($table);
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
            'index' => ListEmploymentStatuses::route('/'),
            'create' => CreateEmploymentStatus::route('/create'),
            // 'view' => ViewEmploymentStatus::route('/{record}'),
            'edit' => EditEmploymentStatus::route('/{record}/edit'),
        ];
    }
}
