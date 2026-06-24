<?php

namespace App\Filament\Resources\PayrollAdjustments;

use App\Filament\Resources\PayrollAdjustments\Pages\CreatePayrollAdjustment;
use App\Filament\Resources\PayrollAdjustments\Pages\EditPayrollAdjustment;
use App\Filament\Resources\PayrollAdjustments\Pages\ListPayrollAdjustments;
use App\Filament\Resources\PayrollAdjustments\Schemas\PayrollAdjustmentForm;
use App\Filament\Resources\PayrollAdjustments\Tables\PayrollAdjustmentsTable;
use App\Models\PayrollAdjustment;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PayrollAdjustmentResource extends Resource
{
    protected static ?string $model = PayrollAdjustment::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::CurrencyDollar;

    protected static ?string $navigationLabel = 'Payroll Deductions';

    protected static ?string $modelLabel = 'Payroll Deduction';

    protected static ?string $pluralModelLabel = 'Payroll Deductions';

    protected static string | UnitEnum | null $navigationGroup = 'Payroll Management';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return PayrollAdjustmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollAdjustmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrollAdjustments::route('/'),
            'create' => CreatePayrollAdjustment::route('/create'),
            'edit' => EditPayrollAdjustment::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(static::roleName(), [
            'staff',
            'admin',
            'super admin',
            'owner',
        ]);
    }

    public static function canAccess(): bool
    {
        return in_array(static::roleName(), [
            'staff',
            'admin',
            'super admin',
            'owner',
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'employee.branch',
                'payrollPeriod',
            ]);

        return static::applyBranchRestriction($query);
    }

    public static function applyBranchRestriction(Builder $query): Builder
    {
        $user = Filament::auth()->user();
        $roleName = static::roleName();

        if ($roleName === 'staff') {
            if (! $user?->branch_id) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('employee', function (Builder $employeeQuery) use ($user) {
                $employeeQuery->where('branch_id', $user->branch_id);
            });
        }

        return $query;
    }

    protected static function roleName(): ?string
    {
        return strtolower(trim(Filament::auth()->user()?->role?->role_name ?? ''));
    }
}