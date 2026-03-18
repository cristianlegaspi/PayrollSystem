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

        // If no user, return empty (extra safety)
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // ✅ Admin / Super Admin → see all
        if (in_array($roleName, ['Admin', 'Super Admin'])) {
            return $query;
        }

        // ✅ If branch is "All Branch" → see all
        if ($user->branch?->branch_name === 'All Branch') {
            return $query;
        }

        // ✅ All other users → restrict to their branch
        return $query->whereHas('employee', function ($q) use ($user) {
            $q->where('branch_id', $user->branch_id);
        });
    }
}
