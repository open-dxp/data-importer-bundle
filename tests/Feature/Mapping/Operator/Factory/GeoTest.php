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

use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\AsGeobounds;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\AsGeopoint;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\AsGeopolygon;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\AsGeopolyline;
use OpenDxp\Model\DataObject\Data\GeoCoordinates;
use OpenDxp\TestFoundation\Container;

const SALZBURG = [
    '47.83595982332057',
    '13.06517167884434',
];
const ANIF = [
    '47.810540991091045',
    '13.073721286556358',
];

/**
 * @return list<list<float>>
 */
function coordinates(GeoCoordinates ...$points): array
{
    return array_map(
        static fn (GeoCoordinates $point): array => [
            $point->getLatitude(),
            $point->getLongitude(),
        ],
        $points,
    );
}

it('reads a geopoint from latitude and longitude', function () {
    $point = Container::get(AsGeopoint::class)->process(SALZBURG);

    expect(coordinates($point))->toEqual([SALZBURG]);
});

it('reads geobounds from the north east and the south west corner', function () {
    $bounds = Container::get(AsGeobounds::class)->process([
        ...SALZBURG,
        ...ANIF,
    ]);

    expect(coordinates($bounds->getNorthEast(), $bounds->getSouthWest()))->toEqual([
        SALZBURG,
        ANIF,
    ]);
});

it('reads the points of a line or a polygon from a flat list or from pairs', function (string $operator, array $input) {
    $points = Container::get($operator)->process($input);

    expect(coordinates(...$points))->toEqual([
        SALZBURG,
        ANIF,
    ]);
})->with([
    'a polygon' => [AsGeopolygon::class],
    'a line' => [AsGeopolyline::class],
])->with([
    'from a flat list' => [[...SALZBURG, ...ANIF]],
    'from pairs' => [[SALZBURG, ANIF]],
]);
