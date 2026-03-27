<?php

namespace App\Filament\Resources\Payrolls\Pages;

use App\Filament\Resources\Payrolls\PayrollResource;
use App\Services\PayrollService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Models\PayrollPeriod;
use App\Models\Payroll;
use App\Models\Branch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
      // ================= GENERATE PAYROLL =================
            Action::make('generatePayroll')
                ->label('Generate Payroll')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary')


                // ✅ ROLE-BASED VISIBILITY
                ->visible(fn () => 
                    Auth::check() &&
                    in_array(optional(Auth::user()->role)->role_name, ['Admin', 'Super Admin'])
                )

                ->form([
                    Select::make('payroll_period_id')
                        ->label('Payroll Period')
                        ->relationship(
                            name: 'payrollPeriod',
                            titleAttribute: 'description',
                            modifyQueryUsing: fn ($query) => $query->where('status', 'open')
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                ])

                ->requiresConfirmation()

                ->action(function (array $data) {

                    try {

                        $period = PayrollPeriod::findOrFail($data['payroll_period_id']);

                        $service = new PayrollService();
                        $service->computePayrollForPeriod($period);

                        Notification::make()
                            ->title('Payroll generated successfully!')
                            ->success()
                            ->send();

                    } catch (\Throwable $e) {

                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

        ];
        
    }

     protected ?string $heading = 'Payroll Management';
    protected ?string $subheading = 'Overview of All Payroll';
}