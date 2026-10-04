<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\DataImporterBundle\Tests\Feature\Mapping\Operator\Factory;

use Exception;
use OpenDxp\Bundle\DataImporterBundle\Exception\InvalidConfigurationException;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\AsColor;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\AsCountries;
use OpenDxp\TestFoundation\Container;

it('reads a color from its channels or from a hex code', function (mixed $input) {
    $color = Container::get(AsColor::class)->process($input);

    expect([$color->getR(), $color->getG(), $color->getB(), $color->getA()])->toBe([15, 44, 73, 255]);
})->with([
    'channels' => [[15, 44, 73, 255]],
    'a hex code' => ['#0f2c49FF'],
]);

it('refuses a hex code of the wrong length', function () {
    Container::get(AsColor::class)->process('#0f2c49F');
})->throws(Exception::class);

it('reads country codes from country names and ignores the spaces around them', function (array $names) {
    expect(Container::get(AsCountries::class)->process($names))->toBe(['CN', 'DE', 'AT']);
})->with([
    'plain names' => [['China', 'Germany', 'Austria']],
    'names with spaces' => [['China ', ' Germany', ' Austria ']],
]);

it('reads country names only from an array', function () {
    Container::get(AsCountries::class)->evaluateReturnType('default');
})->throws(InvalidConfigurationException::class);
