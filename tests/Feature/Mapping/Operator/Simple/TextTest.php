<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\DataImporterBundle\Tests\Feature\Mapping\Operator\Simple;

use OpenDxp\Bundle\DataImporterBundle\Exception\InvalidConfigurationException;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Simple\StaticText;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Simple\StringReplace;
use OpenDxp\TestFoundation\Container;

function stringReplace(string $search, string $replace): StringReplace
{
    $operator = Container::get(StringReplace::class);
    $operator->setSettings([
        'search' => $search,
        'replace' => $replace,
    ]);

    return $operator;
}

function staticText(string $text, bool $alwaysAdd): StaticText
{
    $operator = Container::get(StaticText::class);
    $operator->setSettings([
        'mode' => StaticText::MODE_APPEND,
        'text' => $text,
        'alwaysAdd' => $alwaysAdd,
    ]);

    return $operator;
}

it('replaces a text in a value and in every value of an array', function (mixed $input, mixed $expected) {
    $value = stringReplace('Test', 'Result')->process($input);

    expect($value)->toBe($expected);
})->with([
    'a value' => ['Hello Test', 'Hello Result'],
    'an array' => [
        ['Hello Test', 'Test Array', '*Test*'],
        ['Hello Result', 'Result Array', '*Result*'],
    ],
    'an array with 0' => [['Test', 'Test 0', '0'], ['Result', 'Result 0', '0']],
]);

it('removes a text from a value and from every value of an array', function (
    string $search,
    mixed $input,
    mixed $expected,
) {
    $value = stringReplace($search, '')->process($input);

    expect($value)->toBe($expected);
})->with([
    'a value that becomes 0' => ['ObjectKey ', 'ObjectKey 0', '0'],
    'a value that becomes empty' => ['ObjectKey', 'ObjectKey', ''],
    'an array with empty values' => ['Test', ['Hello Test', '', 'Test'], ['Hello ', '', '']],
]);

it('replaces only in texts', function () {
    stringReplace('Test', 'Result')->evaluateReturnType('boolean');
})->throws(InvalidConfigurationException::class);

it('appends a static text to a value', function (
    string $text,
    bool $alwaysAdd,
    string $input,
    string $expected,
) {
    $value = staticText($text, $alwaysAdd)->process($input);

    expect($value)->toBe($expected);
})->with([
    'the text 0' => ['0', false, 'Test', 'Test0'],
    'the text 0 to an empty value when it is always added' => ['0', true, '', '0'],
    'a text to the value 0' => ['px', false, '0', '0px'],
    'nothing to an empty value' => ['px', false, '', ''],
]);
