<?php

declare(strict_types=1);

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\DataImporterBundle\Tests\Feature\Mapping\Operator\Factory;

use Carbon\Carbon;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\Date;
use OpenDxp\TestFoundation\Container;

/**
 * @param array<string, string> $settings
 */
function dateOperator(array $settings): Date
{
    $operator = Container::get(Date::class);
    $operator->setSettings($settings);

    return $operator;
}

it('reads a date in the format Y-m-d unless the settings name another one', function (array $settings, string $input) {
    $date = dateOperator($settings)->process($input);

    expect($date)
        ->toBeInstanceOf(Carbon::class)
        ->and($date->format('Y-m-d'))
        ->toBe('2022-08-01');
})->with([
    'the default format' => [[], '2022-08-01'],
    'a format of the settings' => [['format' => 'Y.m.d'], '2022.08.01'],
]);

it('previews a date in ISO 8601', function () {
    $date = Carbon::create(2022, 8, 1);

    $preview = dateOperator([])->generateResultPreview($date);

    expect($preview)->toBe($date->format('c'));
});

it('reads every date of an array', function () {
    $dates = dateOperator(['format' => 'Y.m.d'])->process([
        '2022.08.01',
        '1950.09.01',
        '2250.12.01',
    ]);

    expect(array_map(static fn (Carbon $date): string => $date->format('Y-m'), $dates))->toBe([
        '2022-08',
        '1950-09',
        '2250-12',
    ]);
});

it('previews every date of an array in ISO 8601', function () {
    $dates = [
        Carbon::create(2022, 8, 1),
        Carbon::create(1950, 9, 1),
    ];

    $preview = dateOperator([])->generateResultPreview($dates);

    expect($preview)->toBe([
        $dates[0]->format('c'),
        $dates[1]->format('c'),
    ]);
});

it('takes an empty value for no date', function () {
    $date = dateOperator([])->process('');

    expect($date)->toBeNull();
});
