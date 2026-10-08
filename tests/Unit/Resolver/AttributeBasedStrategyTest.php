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

namespace OpenDxp\Bundle\DataImporterBundle\Tests\Unit\Resolver;

use OpenDxp\Bundle\DataImporterBundle\Exception\InvalidConfigurationException;
use OpenDxp\Bundle\DataImporterBundle\Resolver\Publish\AttributeBasedStrategy;
use OpenDxp\Model\Document\Page;

it('publishes an element by the column the data source index names', function (int|string $index) {
    $strategy = new AttributeBasedStrategy();
    $strategy->setSettings(['dataSourceIndex' => $index]);
    $row = [
        0 => false,
        1 => false,
        12 => false,
        $index => true,
    ];

    $page = $strategy->updatePublishState(new Page(), false, $row);

    expect($page->isPublished())->toBeTrue();
})->with([
    'the text 0' => ['0'],
    'the number 0' => [0],
    'the number 1' => [1],
    'the text 12' => ['12'],
]);

it('leaves an element unpublished when its row has no such column', function () {
    $strategy = new AttributeBasedStrategy();
    $strategy->setSettings(['dataSourceIndex' => 3]);

    $page = $strategy->updatePublishState((new Page())->setPublished(true), false, ['a', 'b']);

    expect($page->isPublished())->toBeFalse();
});

it('refuses a configuration without a data source index', function () {
    (new AttributeBasedStrategy())->setSettings([]);
})->throws(InvalidConfigurationException::class);
