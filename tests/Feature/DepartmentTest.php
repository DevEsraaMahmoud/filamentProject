<?php

use App\Filament\Resources\DepartmentResource;
use App\Models\Department;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

// Form and Views Testing

it('can render list departments page', function () {
    $this->get(DepartmentResource::getUrl('index'))->assertSuccessful();
});


it('can list departments', function () {
    $departments = Department::factory()->count(10)->create();

    livewire(DepartmentResource\Pages\ListDepartments::class)
        ->assertCanSeeTableRecords($departments);
});

it('can render create department page', function () {
    $this->get(DepartmentResource::getUrl('create'))->assertSuccessful();
});


it('can create department', function () {
    $department = Department::factory()->make();

    livewire(DepartmentResource\Pages\CreateDepartment::class)
        ->fillForm([
            'name' => $department->name,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Department::class, [
        'name' => $department->name,
    ]);
});

it('can validate input on create', function () {
    livewire(DepartmentResource\Pages\CreateDepartment::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can render edit department page', function () {
    $this->get(DepartmentResource::getUrl('edit', [
        'record' => Department::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve department data', function () {
    $department = Department::factory()->create();

    livewire(DepartmentResource\Pages\EditDepartment::class, [
        'record' => $department->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $department->name,
        ]);
});

it('can update department data', function () {
    $department = Department::factory()->create();
    $newData = Department::factory()->make();

    livewire(DepartmentResource\Pages\EditDepartment::class, [
        'record' => $department->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($department->refresh())
        // ->author_id->toBe($newData->author->getKey())
        ->name->toBe($newData->name);
});


it('can validate input on update', function () {
    $department = Department::factory()->create();

    livewire(DepartmentResource\Pages\EditDepartment::class, [
        'record' => $department->getRouteKey(),
    ])
        ->fillForm([
            'name' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can delete department', function () {
    $department = Department::factory()->create();

    livewire(DepartmentResource\Pages\EditDepartment::class, [
        'record' => $department->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($department);
});

it('can render view page', function () {
    $this->get(DepartmentResource::getUrl('view', [
        'record' => Department::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data', function () {
    $department = Department::factory()->create();

    livewire(DepartmentResource\Pages\ViewDepartment::class, [
        'record' => $department->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $department->name,
        ]);
});


// Table Testing

// it('can sort departments by departments count', function () {
//     $departments = Department::factory()->count(10)->create();

//     livewire(DepartmentResource\Pages\ListDepartments::class)
//         ->sortTable('departments_count')
//         ->assertCanSeeTableRecords($departments->sortBy('departments_count'), inOrder: true)
//         ->sortTable('departments_count', 'desc')
//         ->assertCanSeeTableRecords($departments->sortByDesc('departments_count'), inOrder: true);
// });

// // it('can sort departments by full name', function () {
// //     $departments = Department::factory()->count(10)->create();

// //     livewire(DepartmentResource\Pages\ListDepartments::class)
// //         ->sortTable('full_name')
// //         ->assertCanSeeTableRecords($departments->sortBy('full_name'), inOrder: true)
// //         ->sortTable('full_name', 'desc')
// //         ->assertCanSeeTableRecords($departments->sortByDesc('full_name'), inOrder: true);
// // });

// it('can sort departments by full name', function () {
//     $departments = Department::factory()->count(10)->create();

//     // Compute the full_name for each department for sorting purposes
//     $departments->each(function ($department) {
//         $department->full_name = $department->name . ' ' . $department->last_name;
//     });

//     // Sort departments by full_name in ascending order
//     $sortedDepartmentsAsc = $departments->sortBy('full_name')->values();

//     livewire(DepartmentResource\Pages\ListDepartments::class)
//         ->call('sortTable', 'full_name')
//         ->assertSeeInOrder($sortedDepartmentsAsc->pluck('full_name')->toArray());

//     // Sort departments by full_name in descending order
//     $sortedDepartmentsDesc = $departments->sortByDesc('full_name')->values();

//     livewire(DepartmentResource\Pages\ListDepartments::class)
//         ->call('sortTable', 'full_name', 'desc')
//         ->assertSeeInOrder($sortedDepartmentsDesc->pluck('full_name')->toArray());
// });

// it('can search department by name', function () {
//     $departments = Department::factory()->count(10)->create();

//     $name = $departments->first()->name;

//     livewire(DepartmentResource\Pages\ListDepartments::class)
//         ->searchTable($name)
//         ->assertCanSeeTableRecords($departments->where('name', $name))
//         ->assertCanNotSeeTableRecords($departments->where('name', '!=', $name));
// });