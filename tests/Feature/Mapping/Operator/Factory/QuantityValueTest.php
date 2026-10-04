<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\DataImporterBundle\Tests\Feature\Mapping\Operator\Factory;

use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\InputQuantityValue;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\InputQuantityValueArray;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\QuantityValue;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\QuantityValueArray;
use OpenDxp\Model\DataObject\Data\InputQuantityValue as InputQuantityValueData;
use OpenDxp\Model\DataObject\Data\QuantityValue as QuantityValueData;
use OpenDxp\Test\Factory\QuantityValueUnitFactory;
use OpenDxp\TestFoundation\Container;

beforeEach(function () {
    QuantityValueUnitFactory::createOne(['id' => 'm', 'abbreviation' => 'm', 'longname' => 'Meter']);
});

function quantityValue(array $settings = []): QuantityValue
{
    $operator = Container::get(QuantityValue::class);
    $operator->setSettings($settings);

    return $operator;
}

/**
 * @return array{0: float|null, 1: string|null}|null
 */
function valueAndUnit(?QuantityValueData $quantity): ?array
{
    return $quantity === null ? null : [$quantity->getValue(), $quantity->getUnitId()];
}

it('reads a quantity value and finds its unit as the settings say', function (array $settings, mixed $input, ?array $expected) {
    expect(valueAndUnit(quantityValue($settings)->process($input)))->toBe($expected);
})->with([
    'a unit by its id' => [[], ['12', 'm'], [12.0, 'm']],
    'no unit' => [[], ['12'], [12.0, null]],
    'a unit without a value' => [[], [null, 'm'], [null, 'm']],
    'nothing' => [[], [], null],
    'two empty texts' => [[], ['', ''], null],
    'a unit by its abbreviation' => [['unitSourceSelect' => 'abbr'], ['12', 'm'], [12.0, 'm']],
    'the static unit' => [['unitSourceSelect' => 'static', 'staticUnitSelect' => 'm'], ['12'], [12.0, 'm']],
    'the static unit over the one of the row' => [['unitSourceSelect' => 'static', 'staticUnitSelect' => 'm'], ['12', 'km'], [12.0, 'm']],
    'the static unit for a single value' => [['unitSourceSelect' => 'static', 'staticUnitSelect' => 'm'], '12', [12.0, 'm']],
    'the static unit without a value' => [['unitSourceSelect' => 'static', 'staticUnitSelect' => 'm'], [], [null, 'm']],
]);

it('drops the unit without a value when the settings say so', function (array $settings, array $input) {
    expect(quantityValue([...$settings, 'unitNullIfNoValueCheckbox' => true])->process($input))->toBeNull();
})->with([
    'a unit by its abbreviation' => [['unitSourceSelect' => 'abbr']],
    'the static unit' => [['unitSourceSelect' => 'static', 'staticUnitSelect' => 'm']],
])->with([
    'with a unit in the row' => [[null, 'm']],
    'with an empty row' => [[]],
]);

it('resolves the unit of a quantity value', function () {
    $operator = quantityValue();
    $quantity = $operator->process(['12', 'm']);

    expect($quantity->getUnit()->getLongname())->toBe('Meter')
        ->and($operator->generateResultPreview($quantity))->toStartWith('Quantity');
});

it('reads an input quantity value with the unit of an abbreviation', function (array $input, ?string $value, string $unit) {
    $operator = Container::get(InputQuantityValue::class);
    $quantity = $operator->process($input);

    expect($quantity)->toBeInstanceOf(InputQuantityValueData::class)
        ->and($quantity->getValue())->toBe($value)
        ->and((string) $quantity->getUnitId())->toBe($unit)
        ->and($operator->generateResultPreview($quantity))->toStartWith('InputQuantity');
})->with([
    'a value and a unit' => [['12', 'm'], '12', 'm'],
    'a value' => [['12'], '12', ''],
    'a unit' => [[null, 'm'], null, 'm'],
]);

it('reads one quantity value per row', function (string $operator, string $data) {
    $quantities = Container::get($operator)->process([['12', 'm'], ['12'], [null, 'm']]);

    expect($quantities)->each->toBeInstanceOf($data)
        ->and(array_map(static fn ($quantity): array => [$quantity->getValue(), $quantity->getUnitId()], $quantities))
        ->toEqual([['12', 'm'], ['12', null], [null, 'm']])
        ->and($quantities[0]->getUnit()->getLongname())->toBe('Meter')
        ->and(Container::get($operator)->generateResultPreview($quantities)[0])->toContain('Quantity');
})->with([
    'quantity values' => [QuantityValueArray::class, QuantityValueData::class],
    'input quantity values' => [InputQuantityValueArray::class, InputQuantityValueData::class],
]);
