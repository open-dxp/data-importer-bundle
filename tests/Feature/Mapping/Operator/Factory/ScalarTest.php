<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\DataImporterBundle\Tests\Feature\Mapping\Operator\Factory;

use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\AsArray;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\Boolean;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\Numeric;
use OpenDxp\TestFoundation\Container;

it('wraps a single value into an array and keeps an array', function (mixed $input, array $expected) {
    expect(Container::get(AsArray::class)->process($input))->toBe($expected);
})->with([
    'a single value' => ['some value', ['some value']],
    'an array' => [['some value'], ['some value']],
]);

it('reads a boolean from the first value', function (mixed $input, bool $expected) {
    expect(Container::get(Boolean::class)->process($input))->toBe($expected);
})->with([
    'true' => [true, true],
    'an array' => [[true, false], true],
    'the text false' => ['false', false],
    'the text true' => ['true', true],
    'the text 0' => ['0', false],
]);

it('reads a number from the first value', function (mixed $input, float $expected) {
    expect(Container::get(Numeric::class)->process($input))->toBe($expected);
})->with([
    'a text' => ['123', 123.0],
    'an array' => [['123.7'], 123.7],
]);
