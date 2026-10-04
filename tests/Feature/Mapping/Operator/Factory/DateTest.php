<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\DataImporterBundle\Tests\Feature\Mapping\Operator\Factory;

use Carbon\Carbon;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\Date;
use OpenDxp\TestFoundation\Container;

function dateOperator(array $settings = []): Date
{
    $operator = Container::get(Date::class);
    $operator->setSettings($settings);

    return $operator;
}

it('reads a date in the format Y-m-d unless the settings name another one', function (array $settings, string $input) {
    $date = dateOperator($settings)->process($input);

    expect($date)->toBeInstanceOf(Carbon::class)
        ->and($date->format('Y-m-d'))->toBe('2022-08-01')
        ->and(dateOperator($settings)->generateResultPreview($date))->toBe($date->format('c'));
})->with([
    'the default format' => [[], '2022-08-01'],
    'a format of the settings' => [['format' => 'Y.m.d'], '2022.08.01'],
]);

it('reads every date of an array', function () {
    $dates = dateOperator(['format' => 'Y.m.d'])->process(['2022.08.01', '1950.09.01', '2250.12.01']);

    expect(array_map(static fn (Carbon $date): string => $date->format('Y-m'), $dates))->toBe(['2022-08', '1950-09', '2250-12'])
        ->and(dateOperator()->generateResultPreview($dates))->toBe(array_map(static fn (Carbon $date): string => $date->format('c'), $dates));
});

it('takes an empty value for no date', function () {
    expect(dateOperator()->process(''))->toBeNull();
});
