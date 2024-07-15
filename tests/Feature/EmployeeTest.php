<?php

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Log;
use function Pest\Livewire\livewire;
use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\EmployeeResource\Pages\EditEmployee;
use App\Filament\Resources\DepartmentResource\Pages\EditDepartment;
use App\Filament\Resources\EmployeeResource\RelationManagers\DepartmentsRelationManager;
use App\Models\Admin;


it('can render list employees page', function () {
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-create'])->create();
    $this->actingAs($admin, 'admin')
        ->get(EmployeeResource::getUrl('index'))
        ->assertSuccessful();
});

it("can't render list employees page with no permission", function () {
    $PermissionFactory = new PermissionFactory();
    $PermissionFactory->create(['employee-view', 'employee-create']);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(EmployeeResource::getUrl('index'))
        ->assertForbidden();
});

it('prevent access to a regular user to admin panel and redirect to login page', function(){
    $this->actingAs(User::factory()->create(), 'web')
    ->get(EmployeeResource::getUrl('index'))
    ->assertStatus(302);
});

it('can list employees', function () {
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-create'])->create();

    // Log in as the admin
    $this->actingAs($admin, 'admin');

    // Create employees and their related departments
    $employee = Employee::factory()
        ->has(Department::factory()->count(10))
        ->create();

    // Test the Livewire component
    livewire(DepartmentsRelationManager::class, [
        'ownerRecord' => $employee,
        'pageClass' => EditEmployee::class,
    ])->assertCanSeeTableRecords($employee->departments);
});


it('can render relation manager', function () {
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-create'])->create();

    $this->actingAs($admin, 'admin');

    $dep = Employee::factory()
        ->has(Department::factory()->count(10))
        ->create();

    livewire(DepartmentsRelationManager::class, [
        'ownerRecord' => $dep,
        'pageClass' => EditDepartment::class,
    ])->assertSuccessful();
});

// it('can render relation manager', function () {
//     $adminFactory = new AdminWithPermissionsFactory();
//     $admin = $adminFactory->withPermissions(['employee-view', 'employee-create'])->create();

//     $this->actingAs($admin, 'admin');

//     $employees = Employee::factory()
//         ->has(Department::factory()->count(10))
//         ->create();

//     livewire(DepartmentsRelationManager::class, [
//         'ownerRecord' => $employees,
//         'pageClass' => EditDepartment::class,
//     ])->assertSuccessful();
// });

it('can render create employee page', function () {
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-create'])->create();

    $this->actingAs($admin, 'admin');

    $this->get(EmployeeResource::getUrl('create'))->assertSuccessful();
});


it('can create employee', function () {
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-create'])->create();

    $this->actingAs($admin, 'admin');

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
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-create'])->create();

    $this->actingAs($admin, 'admin');

    livewire(EmployeeResource\Pages\CreateEmployee::class)
        ->fillForm([
            'first_name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['first_name' => 'required']);
});

it('can retrieve employee data', function () {
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-create', 'employee-update', 'employee-delete'])->create();

    $this->actingAs($admin, 'admin');

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
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-delete', 'employee-update'])->create();

    $this->actingAs($admin, 'admin');
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
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-delete', 'employee-update'])->create();

    $this->actingAs($admin, 'admin');

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
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-update', 'employee-delete', 'employee-view'])->create();

    $this->actingAs($admin, 'admin');

    $employee = Employee::factory()->create();

    livewire(EmployeeResource\Pages\EditEmployee::class, [
        'record' => $employee->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($employee);
});

it('can render view page', function () {
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-update'])->create();

    $this->actingAs($admin, 'admin');

    $this->get(EmployeeResource::getUrl('view', [
        'record' => Employee::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data', function () {
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-update'])->create();

    $this->actingAs($admin, 'admin');

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
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-create', 'employee-update', 'employee-delete'])->create();

    $this->actingAs($admin, 'admin');

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
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-create', 'employee-update', 'employee-delete'])->create();

    $this->actingAs($admin, 'admin');

    $employees = Employee::factory()->count(10)->create();

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->sortTable('departments_count')
        ->assertCanSeeTableRecords($employees->sortBy('departments_count'), inOrder: true)
        ->sortTable('departments_count', 'desc')
        ->assertCanSeeTableRecords($employees->sortByDesc('departments_count'), inOrder: true);
});

it('can sort employees by full name in desc order', function () {
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-create', 'employee-update', 'employee-delete'])->create();

    $this->actingAs($admin, 'admin');

    $employees = Employee::factory()->count(10)->create();

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->sortTable('full_name')
        ->assertCanSeeTableRecords($employees->sortBy('full_name'), inOrder: true)
        ->sortTable('full_name', 'desc')
        ->assertCanSeeTableRecords($employees->sortByDesc('full_name'), inOrder: true);
});

it('can sort employees by full name in asc order', function () {
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-create', 'employee-update', 'employee-delete'])->create();

    $this->actingAs($admin, 'admin');

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
    $adminFactory = new AdminWithPermissionsFactory();
    $admin = $adminFactory->withPermissions(['employee-view', 'employee-create', 'employee-update', 'employee-delete'])->create();

    $this->actingAs($admin, 'admin');

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

it('ensures a regular user cannot access the admin panel', function () {
    // Create a regular user
    $user = User::factory()->create();

    // Acting as the regular user
    $this->actingAs($user);

    // Attempt to access the admin panel
    $response = $this->get('/admin/employees');

    // Assert that the response is a redirect to the login page
    $response->assertStatus(302);
})->only();

// it('can attach department to employee', function () {
//     $adminFactory = new AdminWithPermissionsFactory();
//     $admin = $adminFactory->withPermissions(['employee-view', 'employee-create'])->create();

//     // Log in as the admin
//     $this->actingAs($admin, 'admin');

//     // Create an employee
//     $employee = Employee::factory()->create();

//     // Create a department
//     $department = Department::factory()->create();

//     // Test the Livewire component for attaching the department
//     $this->get(EmployeeResource::class, [
//         'ownerRecord' => $employee,
//         'pageClass' => EditEmployee::class,
//     ])
//     ->set('departments', [$department->id])
//     ->call('attachDepartments') // Assuming there's a method to attach departments
//     ->assertHasNoErrors();

//     // Refresh the employee to get the latest data
//     $employee->refresh();

//     // Assert the department is attached to the employee
//     $this->assertTrue($employee->departments->contains($department));
// })->only();
