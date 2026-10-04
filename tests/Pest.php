<?php

declare(strict_types=1);

use OpenDxp\TestFoundation\TestCase;
use Zenstruck\Foundry\Test\Factories;

pest()->extend(TestCase::class)->use(Factories::class)->in('Feature');
