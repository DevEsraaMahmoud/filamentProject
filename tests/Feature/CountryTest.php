<?php

use App\Filament\Resources\CountryResource;
use App\Models\Country;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

// Form and Views Testing

it('can render list countrys page', function () {
    $this->get(CountryResource::getUrl('index'))->assertSuccessful();
});


it('can list countrys', function () {
    $countrys = Country::factory()->count(10)->create();

    livewire(CountryResource\Pages\ListCountries::class)
        ->assertCanSeeTableRecords($countrys);
});

it('can render create country page', function () {
    $this->get(CountryResource::getUrl('create'))->assertSuccessful();
});


it('can create country', function () {
    $country = Country::factory()->make();

    livewire(CountryResource\Pages\CreateCountry::class)
        ->fillForm([
            'name' => $country->name,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Country::class, [
        'name' => $country->name,
    ]);
});

it('can validate input on create', function () {
    livewire(CountryResource\Pages\CreateCountry::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can render edit country page', function () {
    $this->get(CountryResource::getUrl('edit', [
        'record' => Country::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve country data', function () {
    $country = Country::factory()->create();

    livewire(CountryResource\Pages\EditCountry::class, [
        'record' => $country->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $country->name,
        ]);
});

it('can update country data', function () {
    $country = Country::factory()->create();
    $newData = Country::factory()->make();

    livewire(CountryResource\Pages\EditCountry::class, [
        'record' => $country->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($country->refresh())
        // ->author_id->toBe($newData->author->getKey())
        ->name->toBe($newData->name);
});


it('can validate input on update', function () {
    $country = Country::factory()->create();

    livewire(CountryResource\Pages\EditCountry::class, [
        'record' => $country->getRouteKey(),
    ])
        ->fillForm([
            'name' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can delete country', function () {
    $country = Country::factory()->create();

    livewire(CountryResource\Pages\EditCountry::class, [
        'record' => $country->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($country);
});

it('can render view page', function () {
    $this->get(CountryResource::getUrl('view', [
        'record' => Country::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data', function () {
    $country = Country::factory()->create();

    livewire(CountryResource\Pages\ViewCountry::class, [
        'record' => $country->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $country->name,
        ]);
});