<?php

use App\Filament\Resources\DepartmentResource\Pages\EditDepartment;
use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\EmployeeResource\Pages\EditEmployee;
use App\Models\Department;
use App\Models\Employee;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

// Form and Views Testing

it('can render list employees page', function () {
    $this->get(EmployeeResource::getUrl('index'))->assertSuccessful();
});


it('can list employees', function () {
    $employees = Employee::factory()->count(10)->create();

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->assertCanSeeTableRecords($employees);
});

it('can render create employee page', function () {
    $this->get(EmployeeResource::getUrl('create'))->assertSuccessful();
});


it('can create employee', function () {
    $employee = Employee::factory()->make();

    livewire(EmployeeResource\Pages\CreateEmployee::class)
        ->fillForm([
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'address' => $employee->address,
            'date_hired' => $employee->date_hired,
            'country_id' => $employee->country_id,
            'state_id' => $employee->state_id,
            'city_id' => $employee->city_id,
            'status' => $employee->status,
            'departments' => Department::factory()->count(3)->create(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Employee::class, [
        'first_name' => $employee->first_name,
        'last_name' => $employee->last_name,
        'address' => $employee->address,
        'date_hired' => $employee->date_hired,
        'country_id' => $employee->country_id,
        'state_id' => $employee->state_id,
        'city_id' => $employee->city_id,
        'status' => $employee->status,
    ]);
});

it('can validate input on create', function () {
    livewire(EmployeeResource\Pages\CreateEmployee::class)
        ->fillForm([
            'first_name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['first_name' => 'required']);
});

it('can render edit employee page', function () {
    $this->get(EmployeeResource::getUrl('edit', [
        'record' => Employee::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve employee data', function () {
    $employee = Employee::factory()->create();

    livewire(EmployeeResource\Pages\EditEmployee::class, [
        'record' => $employee->getRouteKey(),
    ])
        ->assertFormSet([
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'address' => $employee->address,
            'date_hired' => $employee->date_hired,
            'country_id' => $employee->country_id,
            'state_id' => $employee->state_id,
            'city_id' => $employee->city_id,
            'status' => $employee->status,
        ]);
});

it('can update employee data', function () {
    $employee = Employee::factory()->create();
    $newData = Employee::factory()->make();

    livewire(EmployeeResource\Pages\EditEmployee::class, [
        'record' => $employee->getRouteKey(),
    ])
        ->fillForm([
            'first_name' => $newData->first_name,
            'last_name' => $newData->last_name,
            'address' => $newData->address,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($employee->refresh())
        // ->author_id->toBe($newData->author->getKey())
        ->first_name->toBe($newData->first_name)
        ->last_name->toBe($newData->last_name)
        ->address->toBe($newData->address);
});


it('can validate input on update', function () {
    $employee = Employee::factory()->create();

    livewire(EmployeeResource\Pages\EditEmployee::class, [
        'record' => $employee->getRouteKey(),
    ])
        ->fillForm([
            'first_name' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['first_name' => 'required']);
});

it('can delete employee', function () {
    $employee = Employee::factory()->create();

    livewire(EmployeeResource\Pages\EditEmployee::class, [
        'record' => $employee->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($employee);
});

it('can render view page', function () {
    $this->get(EmployeeResource::getUrl('view', [
        'record' => Employee::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data', function () {
    $employee = Employee::factory()->create();

    livewire(EmployeeResource\Pages\ViewEmployee::class, [
        'record' => $employee->getRouteKey(),
    ])
        ->assertFormSet([
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'address' => $employee->address,
            'country_id' => $employee->country_id,
            'state_id' => $employee->state_id,
            'city_id' => $employee->city_id,
            'status' => $employee->status,
        ]);
});

it('can list departments employees', function () {
    $employees = Employee::factory()
        ->has(Department::factory()->count(10))
        ->create();

    livewire(EmployeeResource\RelationManagers\DepartmentsRelationManager::class, [
        'ownerRecord' => $employees,
        'pageClass' => EditEmployee::class,
    ])
        ->assertCanSeeTableRecords($employees->departments);
});


// Table Testing
