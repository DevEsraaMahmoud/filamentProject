<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers\DepartmentsRelationManager;
use App\Models\City;
use App\Models\Employee;
use App\Models\State;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use stdClass;
use Filament\Tables\Contracts\HasTable;


class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([Section::make('Employee Details')
                ->schema([
                    TextInput::make('first_name')
                        ->rules(['required', 'max:255', 'string'])
                        ->required()
                        ->maxLength(255),
                    TextInput::make('last_name')
                        ->rules(['required', 'max:255', 'string'])
                        ->required()
                        ->maxLength(255),
                    FileUpload::make('image')
                        ->label('Upload Image')
                        ->disk('public')
                        ->directory('Images')
                        ->image()
                        ->imageEditor(),
                    Textarea::make('address')
                        ->rules(['required', 'max:255', 'string'])
                        ->required()
                        ->maxLength(255),
                    DatePicker::make('date_hired')
                        ->rules(['date'])
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now())
                        ->required()
                        ->closeOnDateSelection()
                        ->columnSpanFull(),
                    Checkbox::make('status')
                        ->label('Employee Status'),
                ])->columnSpanFull()->columns(2),

            Section::make('Department Details')
                ->collapsed()
                    ->schema([
                        Select::make('country_id')
                            ->relationship(name: 'country', titleAttribute: 'name')
                ->label('Department Country')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('state_id', null);
                                $set('city_id', null);
                            })
                            ->required(),
                        Select::make('state_id')
                            ->options(fn (Get $get): Collection => State::query()
                                ->where('country_id', $get('country_id'))
                                ->pluck('name', 'id'))
                    ->label('Department State')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('city_id', null))
                            ->required(),
                        Select::make('city_id')
                            ->options(fn (Get $get): Collection => City::query()
                                ->where('state_id', $get('state_id'))
                                ->pluck('name', 'id'))
                    ->label('Department City')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('departments')
                            ->relationship(name: 'departments', titleAttribute: 'name')
                ->label('Employee Departments')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->visibleOn('create')
                    ->columnSpan(2)
                            ->required(),
                ])->columnSpan(2)->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                ->defaultImageUrl(url('/images/placeholder.jpg'))
                ->circular(),
            TextColumn::make('Full Name')
                ->getStateUsing(function (Employee $record) {
                    return $record->first_name . ' ' . $record->last_name;
                })
                ->sortable(),
            TextColumn::make('country.name')
                    ->sortable()
                ->searchable(isGlobal: false, isIndividual: true),
                TextColumn::make('address')
                ->searchable(isGlobal: false, isIndividual: true)
                    ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('departments_count')
            ->counts('departments')
                ->sortable(),
                TextColumn::make('date_hired')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(function ($state) {
                    if ($state) {
                        return 'success';
                    } else {
                        return 'danger';
                    }
                })
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->formatStateUsing(function ($state) {
                    if ($state) {
                        return 'Active';
                    } else {
                        return 'InActive';
                    }
                })
            ])
            ->filters([
                SelectFilter::make('country_id')
                ->label('Country')
                ->relationship('country', 'name')
                //  ->multiple()
                ->searchable()
                ->preload(),
                TernaryFilter::make('status')
                ->label('Status')
                ->trueLabel('Active')
                ->falseLabel('InActive')
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DepartmentsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
