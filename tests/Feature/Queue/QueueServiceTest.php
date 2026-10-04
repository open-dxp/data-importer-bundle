<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\DataImporterBundle\Tests\Feature\Queue;

use OpenDxp\Bundle\DataImporterBundle\Processing\ImportProcessingService;
use OpenDxp\Bundle\DataImporterBundle\Queue\QueueService;
use OpenDxp\TestFoundation\Container;

it('keeps an item in the queue until it is processed', function () {
    $queue = Container::get(QueueService::class);
    $queue->addItemToQueue('tmp', ImportProcessingService::EXECUTION_TYPE_SEQUENTIAL, ImportProcessingService::JOB_TYPE_PROCESS, 'some data');

    $ids = $queue->getAllQueueEntryIds(ImportProcessingService::EXECUTION_TYPE_SEQUENTIAL);
    $entry = $queue->getQueueEntryById($ids[0]);

    expect($queue->getQueueItemCount('tmp'))->toBe(1)
        ->and($ids)->toHaveCount(1)
        ->and([$entry['data'], $entry['jobType']])->toBe(['some data', ImportProcessingService::JOB_TYPE_PROCESS]);

    $queue->markQueueEntryAsProcessed($ids[0]);

    expect($queue->getQueueItemCount('tmp'))->toBe(0);
});
