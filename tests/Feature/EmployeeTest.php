<?php

use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Department;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Log;
use function Pest\Livewire\livewire;
use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\EmployeeResource\Pages\EditEmployee;

use App\Filament\Resources\EmployeeResource\Pages\ListEmployees;
use App\Filament\Resources\DepartmentResource\Pages\EditDepartment;

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
            // 'date_hired' => Carbon::parse( $employee->date_hired )->format('Y-m-d'),
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

it('can sort employees by departments count', function () {
    $employees = Employee::factory()->count(10)->create();

    livewire(EmployeeResource\Pages\ListEmployees::class)
    ->sortTable('departments_count')
    ->assertCanSeeTableRecords($employees->sortBy('departments_count'), inOrder: true)
    ->sortTable('departments_count', 'desc')
    ->assertCanSeeTableRecords($employees->sortByDesc('departments_count'), inOrder: true);
});

it('can sort employees by full name in desc order', function () {
    $employees = Employee::factory()->count(10)->create();

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->sortTable('full_name')
        ->assertCanSeeTableRecords($employees->sortBy('full_name'), inOrder: true)
        ->sortTable('full_name', 'desc')
        ->assertCanSeeTableRecords($employees->sortByDesc('full_name'), inOrder: true);
});

it('can sort employees by full name in asc order', function () {
    $employees = Employee::factory()->count(10)->create();

    // Sort employees by full_name in ascending order
    $sortedEmployeesAsc = $employees->sortBy('full_name')->values();

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->call('sortTable', 'full_name')
        ->assertSeeInOrder($sortedEmployeesAsc->pluck('full_name')->toArray());

    // Sort employees by full_name in descending order
    $sortedEmployeesDesc = $employees->sortByDesc('full_name')->values();

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->call('sortTable', 'full_name', 'desc')
        ->assertSeeInOrder($sortedEmployeesDesc->pluck('full_name')->toArray());
});

it('can search posts by first_name', function () {
    $employees = Employee::factory()->count(10)->create();

    $first_name = $employees->first()->first_name;

    try {
        livewire(EmployeeResource\Pages\ListEmployees::class)
            ->searchTable($first_name)
            ->assertCanSeeTableRecords($employees->where('first_name', $first_name))
            ->assertCanNotSeeTableRecords($employees->where('first_name', '!=', $first_name));
    } catch (\Exception $e) {
        Log::error('Test failed for search posts by first_name', [
            'error' => $e->getMessage(),
            'first_name' => $first_name,
            'employee_ids' => $employees->pluck('id')->toArray(),
        ]);
        throw $e;
    }
});
// })->only();