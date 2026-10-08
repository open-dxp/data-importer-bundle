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

use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\Gallery;
use OpenDxp\Bundle\DataImporterBundle\Mapping\Operator\Factory\ImageAdvanced;
use OpenDxp\Model\Asset\Image;
use OpenDxp\Model\DataObject\Data\Hotspotimage;
use OpenDxp\Model\DataObject\Data\ImageGallery;
use OpenDxp\TestFoundation\Container;

function image(string $key): Image
{
    return (new Image())->setKey($key);
}

it('puts a single image into a gallery', function () {
    $gallery = Container::get(Gallery::class)->process(image('first'));

    expect($gallery->getItems())->toHaveCount(1);
});

it('puts every image into a gallery', function () {
    $gallery = Container::get(Gallery::class)->process([
        image('first'),
        image('second'),
    ]);

    expect($gallery->getItems())->toHaveCount(2);
});

it('makes an empty gallery of what is no image', function () {
    $gallery = Container::get(Gallery::class)->process('foo');

    expect($gallery)
        ->toBeInstanceOf(ImageGallery::class)
        ->and($gallery->getItems())
        ->toBe([]);
});

it('previews a gallery as one line per image', function () {
    $operator = Container::get(Gallery::class);
    $gallery = $operator->process([
        image('first'),
        image('second'),
    ]);

    $preview = $operator->generateResultPreview($gallery);

    expect($preview)
        ->toHaveCount(2)
        ->each->toStartWith('GalleryImage');
});

it('makes an advanced image of the first image', function () {
    $image = Container::get(ImageAdvanced::class)->process([
        image('first'),
        image('second'),
    ]);

    expect($image)
        ->toBeInstanceOf(Hotspotimage::class)
        ->and($image->getImage()->getKey())
        ->toBe('first');
});

it('makes an advanced image of a single image', function () {
    $image = Container::get(ImageAdvanced::class)->process(image('single'));

    expect($image->getImage()->getKey())->toBe('single');
});

it('previews an advanced image', function () {
    $operator = Container::get(ImageAdvanced::class);
    $image = $operator->process(image('single'));

    $preview = $operator->generateResultPreview($image);

    expect($preview)->toStartWith('Image Advanced');
});

it('makes no advanced image without an image', function () {
    $image = Container::get(ImageAdvanced::class)->process([]);

    expect($image)->toBeNull();
});
