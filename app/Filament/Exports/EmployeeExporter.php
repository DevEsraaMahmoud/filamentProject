<?php

namespace App\Filament\Exports;

use App\Mail\ExportedFileEmail;
use App\Models\Employee;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;

class EmployeeExporter extends Exporter
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('full_name')
                ->state(function (Employee $record): string {
                    return $record->first_name . ' ' . $record->last_name;
                }),
            ExportColumn::make('country.name'),
            ExportColumn::make('state.name'),
            ExportColumn::make('city.name'),
            ExportColumn::make('address'),
            ExportColumn::make('date_hired'),
            ExportColumn::make('image'),
            // ->enabledByDefault(false) -> does not exist!!?
            // ->columnMapping(false),
            ExportColumn::make('status'),
            ExportColumn::make('departments_count')->counts([
                'departments' => fn (Builder $query) => $query->where('status', true),
            ]),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your employee export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        // $fileName = $export->file_name;
        // $filePath = public_path('exports'). '/' . $export->getFileDirectory() . '/' . $fileName;
        // if ($fileName && file_exists($filePath)) {
        //     Mail::to('esraa.dev@gmail.com')->send(new ExportedFileEmail($fileName, $filePath));
        // }

        return $body;
    }

    public static function getFailedNotificationBody(Export $export): string
    {
        return 'Your employee export has failed with ' . number_format($export->failed_rows) . ' ' . str('row')->plural($export->failed_rows) . '.';
    }

    public function getFileName(Export $export): string
    {
        return 'employee_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
    }

    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         TextInput::make('addressLimit')
    //             ->label('Enter address limit charaters')
    //             ->integer()
    //     ];
    // }

    // public function getJobBatchName(): ?string
    // {
    //     return 'employee-export';
    // }
}
