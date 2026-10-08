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

const STATIC_METER = [
    'unitSourceSelect' => 'static',
    'staticUnitSelect' => 'm',
];

beforeEach(function () {
    QuantityValueUnitFactory::createOne([
        'id' => 'm',
        'abbreviation' => 'm',
        'longname' => 'Meter',
    ]);
});

/**
 * @param array<string, mixed> $settings
 */
function quantityValue(array $settings): QuantityValue
{
    $operator = Container::get(QuantityValue::class);
    $operator->setSettings($settings);

    return $operator;
}

/**
 * @return array{0: float|string|null, 1: string|null}|null
 */
function valueAndUnit(QuantityValueData|InputQuantityValueData|null $quantity): ?array
{
    return $quantity === null ? null : [$quantity->getValue(), $quantity->getUnitId()];
}

it('reads a quantity value and finds its unit as the settings say', function (
    array $settings,
    mixed $input,
    ?array $expected,
) {
    $quantity = quantityValue($settings)->process($input);

    expect(valueAndUnit($quantity))->toBe($expected);
})->with([
    'a unit by its id' => [[], ['12', 'm'], [12.0, 'm']],
    'no unit' => [[], ['12'], [12.0, null]],
    'a unit without a value' => [[], [null, 'm'], [null, 'm']],
    'nothing' => [[], [], null],
    'two empty texts' => [[], ['', ''], null],
    'a unit by its abbreviation' => [['unitSourceSelect' => 'abbr'], ['12', 'm'], [12.0, 'm']],
    'the static unit' => [STATIC_METER, ['12'], [12.0, 'm']],
    'the static unit over the one of the row' => [STATIC_METER, ['12', 'km'], [12.0, 'm']],
    'the static unit for a single value' => [STATIC_METER, '12', [12.0, 'm']],
    'the static unit without a value' => [STATIC_METER, [], [null, 'm']],
]);

it('drops the unit without a value when the settings say so', function (array $settings, array $input) {
    $operator = quantityValue([
        ...$settings,
        'unitNullIfNoValueCheckbox' => true,
    ]);

    $quantity = $operator->process($input);

    expect($quantity)->toBeNull();
})->with([
    'a unit by its abbreviation' => [['unitSourceSelect' => 'abbr']],
    'the static unit' => [STATIC_METER],
])->with([
    'with a unit in the row' => [[null, 'm']],
    'with an empty row' => [[]],
]);

it('resolves the unit of a quantity value', function () {
    $quantity = quantityValue([])->process(['12', 'm']);

    expect($quantity->getUnit()->getLongname())->toBe('Meter');
});

it('previews a quantity value', function () {
    $operator = quantityValue([]);
    $quantity = $operator->process(['12', 'm']);

    $preview = $operator->generateResultPreview($quantity);

    expect($preview)->toStartWith('Quantity');
});

it('reads an input quantity value with the unit of an abbreviation', function (
    array $input,
    ?string $value,
    string $unit,
) {
    $quantity = Container::get(InputQuantityValue::class)->process($input);

    expect($quantity)
        ->toBeInstanceOf(InputQuantityValueData::class)
        ->and($quantity->getValue())
        ->toBe($value)
        ->and((string) $quantity->getUnitId())
        ->toBe($unit);
})->with([
    'a value and a unit' => [['12', 'm'], '12', 'm'],
    'a value' => [['12'], '12', ''],
    'a unit' => [[null, 'm'], null, 'm'],
]);

it('previews an input quantity value', function () {
    $operator = Container::get(InputQuantityValue::class);
    $quantity = $operator->process(['12', 'm']);

    $preview = $operator->generateResultPreview($quantity);

    expect($preview)->toStartWith('InputQuantity');
});

it('reads one quantity value per row', function (string $operator, string $data) {
    $quantities = Container::get($operator)->process([
        ['12', 'm'],
        ['12'],
        [null, 'm'],
    ]);

    expect($quantities)
        ->each->toBeInstanceOf($data)
        ->and(array_map(valueAndUnit(...), $quantities))
        ->toEqual([
            ['12', 'm'],
            ['12', null],
            [null, 'm'],
        ])
        ->and($quantities[0]->getUnit()->getLongname())
        ->toBe('Meter');
})->with([
    'quantity values' => [QuantityValueArray::class, QuantityValueData::class],
    'input quantity values' => [InputQuantityValueArray::class, InputQuantityValueData::class],
]);

it('previews one line per quantity value', function (string $operator) {
    $quantities = Container::get($operator)->process([['12', 'm']]);

    $preview = Container::get($operator)->generateResultPreview($quantities);

    expect($preview[0])->toContain('Quantity');
})->with([
    'quantity values' => [QuantityValueArray::class],
    'input quantity values' => [InputQuantityValueArray::class],
]);
