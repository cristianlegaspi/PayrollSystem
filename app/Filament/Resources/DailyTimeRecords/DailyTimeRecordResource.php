<?php

namespace App\Filament\Resources\DailyTimeRecords;

use App\Filament\Resources\DailyTimeRecords\Pages\CreateDailyTimeRecord;
use App\Filament\Resources\DailyTimeRecords\Pages\EditDailyTimeRecord;
use App\Filament\Resources\DailyTimeRecords\Pages\ListDailyTimeRecords;
use App\Filament\Resources\DailyTimeRecords\Pages\ViewDailyTimeRecord;
use App\Filament\Resources\DailyTimeRecords\Schemas\DailyTimeRecordForm;
use App\Filament\Resources\DailyTimeRecords\Schemas\DailyTimeRecordInfolist;
use App\Filament\Resources\DailyTimeRecords\Tables\DailyTimeRecordsTable;
use App\Models\DailyTimeRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;

class DailyTimeRecordResource extends Resource
{
    protected static ?string $model = DailyTimeRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Clock;

    protected static ?string $recordTitleAttribute = 'DailyTimeRecord';

    protected static string | UnitEnum | null $navigationGroup = 'Attendance Management';
    
    protected static ?string $navigationLabel = 'Daily Time Record (DTR)'; // Custom label

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return DailyTimeRecordForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DailyTimeRecordInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyTimeRecordsTable::configure($table);
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
            'index' => ListDailyTimeRecords::route('/'),
            'create' => CreateDailyTimeRecord::route('/create'),
            // 'view' => ViewDailyTimeRecord::route('/{record}'),
            // 'edit' => EditDailyTimeRecord::route('/{record}/edit'),
        ];
    }

   // Role-aware query for the table
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('employee.branch');

        $user = Filament::auth()->user();
        $roleName = $user?->role?->role_name;

        // Staff: show only records in their branch
        if ($user && $roleName === 'Staff') {
            $query->whereHas('employee.branch', function ($q) use ($user) {
                $q->where('id', $user->branch_id);
            });
        }

        // Admin / Super Admin: see all records, no filter
        return $query;
    }


}
