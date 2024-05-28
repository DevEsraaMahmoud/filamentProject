<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers\DepartmentsRelationManager;
use App\Models\City;
use App\Models\Employee;
use App\Models\State;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Relationships')
                    ->schema([
                        Select::make('country_id')
                            ->relationship(name: 'country', titleAttribute: 'name')
                            ->label('Country')
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
                            ->label('State')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('city_id', null))
                            ->required(),
                        Select::make('city_id')
                            ->options(fn (Get $get): Collection => City::query()
                                ->where('state_id', $get('state_id'))
                                ->pluck('name', 'id'))
                            ->label('City')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('departments')
                            ->relationship(name: 'departments', titleAttribute: 'name')
                            ->label('Departments')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->visibleOn('create')
                            ->required(),
                    ])->columnSpan(1)->columns(2),
                Section::make('Employee Name')
                    ->description('Put the Employee name details in.')
                    ->schema([
                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                    ])->columnSpan(1)->columns(2),
                Section::make('Employee address')
                    ->schema([
                        TextInput::make('address')
                            ->required()
                            ->maxLength(255),
                    ])->columnSpan(1)->columns(2),
                Section::make('Dates')
                    ->schema([
                        DatePicker::make('date_hired')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                    ])->columnSpan(1)->columns(2),
                Section::make('Status')
                ->schema([
                    Checkbox::make('status')
                        ->label('Status')
                    ])->columnSpan(1)->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('country.name')
                    ->sortable()
                    ->searchable(isGlobal: false, isIndividual: true),
                TextColumn::make('first_name')
                    ->searchable(isGlobal:true)
                    ->sortable(),
                TextColumn::make('last_name')
                    ->searchable(),
                TextColumn::make('address')
                    ->searchable(isGlobal:false, isIndividual:true)
                    ->toggleable(isToggledHiddenByDefault: true),
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
                ->color(function($state) {
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
