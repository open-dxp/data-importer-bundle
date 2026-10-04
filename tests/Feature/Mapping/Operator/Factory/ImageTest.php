<?php

declare(strict_types=1);

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

it('puts every image into a gallery', function () {
    $operator = Container::get(Gallery::class);

    expect($operator->process(image('first'))->getItems())->toHaveCount(1)
        ->and($operator->process([image('first'), image('second')])->getItems())->toHaveCount(2);
});

it('makes an empty gallery of what is no image', function () {
    $gallery = Container::get(Gallery::class)->process('foo');

    expect($gallery)->toBeInstanceOf(ImageGallery::class)
        ->and($gallery->getItems())->toBe([]);
});

it('previews a gallery as one line per image', function () {
    $operator = Container::get(Gallery::class);

    expect($operator->generateResultPreview($operator->process([image('first'), image('second')])))
        ->toHaveCount(2)
        ->each->toStartWith('GalleryImage');
});

it('makes an advanced image of the first image', function () {
    $operator = Container::get(ImageAdvanced::class);
    $image = $operator->process([image('first'), image('second')]);

    expect($image)->toBeInstanceOf(Hotspotimage::class)
        ->and($image->getImage()->getKey())->toBe('first')
        ->and($operator->process(image('single'))->getImage()->getKey())->toBe('single')
        ->and($operator->generateResultPreview($image))->toStartWith('Image Advanced');
});

it('makes no advanced image without an image', function () {
    expect(Container::get(ImageAdvanced::class)->process([]))->toBeNull();
});
