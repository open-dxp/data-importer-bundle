<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\DataImporterBundle\Tests\Unit\Resolver;

use OpenDxp\Bundle\DataImporterBundle\Exception\InvalidConfigurationException;
use OpenDxp\Bundle\DataImporterBundle\Resolver\Publish\AttributeBasedStrategy;
use OpenDxp\Model\Document\Page;

it('publishes an element by the column the data source index names', function (int|string $index) {
    $strategy = new AttributeBasedStrategy();
    $strategy->setSettings(['dataSourceIndex' => $index]);

    $page = $strategy->updatePublishState(new Page(), false, [0 => false, 1 => false, 12 => false, $index => true]);

    expect($page->isPublished())->toBeTrue();
})->with(['0', 0, 1, '12']);

it('leaves an element unpublished when its row has no such column', function () {
    $strategy = new AttributeBasedStrategy();
    $strategy->setSettings(['dataSourceIndex' => 3]);

    expect($strategy->updatePublishState((new Page())->setPublished(true), false, ['a', 'b'])->isPublished())->toBeFalse();
});

it('refuses a configuration without a data source index', function () {
    (new AttributeBasedStrategy())->setSettings([]);
})->throws(InvalidConfigurationException::class);
