<?php

namespace App\Filament\Resources\Contributions;

use App\Filament\Resources\Contributions\Pages\CreateContribution;
use App\Filament\Resources\Contributions\Pages\EditContribution;
use App\Filament\Resources\Contributions\Pages\ListContributions;
use App\Filament\Resources\Contributions\Pages\ViewContribution;
use App\Filament\Resources\Contributions\Schemas\ContributionForm;
use App\Filament\Resources\Contributions\Schemas\ContributionInfolist;
use App\Filament\Resources\Contributions\Tables\ContributionsTable;
use App\Models\Contribution;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContributionResource extends Resource
{
    protected static ?string $model = Contribution::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCurrencyDollar;

    protected static ?string $recordTitleAttribute = 'Contribution';

    protected static string | UnitEnum | null $navigationGroup = 'Employee Management';

    protected static ?int $navigationSort = 2;


    public static function form(Schema $schema): Schema
    {
        return ContributionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContributionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContributionsTable::configure($table);
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
            'index' => ListContributions::route('/'),
            'create' => CreateContribution::route('/create'),
            // 'view' => ViewContribution::route('/{record}'),
            'edit' => EditContribution::route('/{record}/edit'),
        ];
    }
}
