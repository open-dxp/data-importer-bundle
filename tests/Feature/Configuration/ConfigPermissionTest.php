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

namespace OpenDxp\Bundle\DataImporterBundle\Tests\Feature\Configuration;

use OpenDxp\Bundle\DataImporterBundle\Settings\ConfigurationPreparationService;
use OpenDxp\Test\Factory\UserFactory;
use OpenDxp\TestFoundation\Admin;
use OpenDxp\TestFoundation\Browser;
use OpenDxp\TestFoundation\Container;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

it('denies the configuration actions without the data hub permission', function (string $method, string $path) {
    Browser::actingAs(UserFactory::createOne())
        ->request($method, sprintf('/admin/opendxpdataimporter/dataobject/config/%s', $path))
        ->assertStatus(403);
})->with([
    'copying a preview' => ['POST', 'copy-preview'],
    'starting an import' => ['PUT', 'start-import'],
    'loading the attributes of a class' => ['GET', 'load-class-attributes'],
]);

it('takes no configuration from the request under a name that does not exist', function () {
    $user = UserFactory::createOne();
    $user->setPermission('plugin_datahub_config', true);
    $user->save();
    Admin::actingAs($user);

    $configuration = fn () => Container::get(ConfigurationPreparationService::class)
        ->prepareConfiguration('unknown', ['loaderConfig' => []]);

    expect($configuration)->toThrow(AccessDeniedHttpException::class);
});
