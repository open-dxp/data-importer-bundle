<?php declare(strict_types=1);

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

namespace OpenDxp\Bundle\DataImporterBundle\Tests\unit;

use Codeception\Test\Unit;
use OpenDxp\Bundle\DataImporterBundle\Resolver\Publish\AttributeBasedStrategy;
use ReflectionObject;

class AttributeBasedStrategyTest extends Unit
{
    protected $tester;

    public function provideIndexes(): array
    {
        return [
            ['0'],
            [0],
            [1],
            ['12'],
        ];
    }

    /**
     * @dataProvider provideIndexes
     */
    public function testDataSourceIndex(mixed $index): void
    {
        $config = ['dataSourceIndex' => $index];
        $strategy = new AttributeBasedStrategy();
        $dataSourceIndex = (new ReflectionObject($strategy))->getProperty('dataSourceIndex');
        $dataSourceIndex->setAccessible(true);

        $strategy->setSettings($config);
        $result = $dataSourceIndex->getValue($strategy);
        self::assertEquals($index, $result);
    }
}
