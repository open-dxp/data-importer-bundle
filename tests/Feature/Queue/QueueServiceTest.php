<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\DataImporterBundle\Tests\Feature\Queue;

use OpenDxp\Bundle\DataImporterBundle\Processing\ImportProcessingService;
use OpenDxp\Bundle\DataImporterBundle\Queue\QueueService;
use OpenDxp\TestFoundation\Container;

beforeEach(function () {
    $this->queue = Container::get(QueueService::class);
});

function queueOneItem(QueueService $queue): void
{
    $queue->addItemToQueue(
        'tmp',
        ImportProcessingService::EXECUTION_TYPE_SEQUENTIAL,
        ImportProcessingService::JOB_TYPE_PROCESS,
        'some data',
    );
}

it('keeps an added item in the queue', function () {
    queueOneItem($this->queue);

    $ids = $this->queue->getAllQueueEntryIds(ImportProcessingService::EXECUTION_TYPE_SEQUENTIAL);

    $entry = $this->queue->getQueueEntryById($ids[0]);
    expect($this->queue->getQueueItemCount('tmp'))
        ->toBe(1)
        ->and($ids)
        ->toHaveCount(1)
        ->and($entry['data'])
        ->toBe('some data')
        ->and($entry['jobType'])
        ->toBe(ImportProcessingService::JOB_TYPE_PROCESS);
});

it('removes an item from the queue once it is processed', function () {
    queueOneItem($this->queue);
    $ids = $this->queue->getAllQueueEntryIds(ImportProcessingService::EXECUTION_TYPE_SEQUENTIAL);

    $this->queue->markQueueEntryAsProcessed($ids[0]);

    expect($this->queue->getQueueItemCount('tmp'))->toBe(0);
});
