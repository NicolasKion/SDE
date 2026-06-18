<?php

declare(strict_types=1);

use NicolasKion\SDE\Data\Dto\IconDto;
use NicolasKion\SDE\Data\Dto\Position2dDto;
use NicolasKion\SDE\Data\Dto\PositionDto;

it('builds a PositionDto from an array', function () {
    $dto = PositionDto::fromArray(['x' => 1.5, 'y' => -2.0, 'z' => 3.25]);

    expect($dto->x)->toBe(1.5)
        ->and($dto->y)->toBe(-2.0)
        ->and($dto->z)->toBe(3.25);
});

it('builds a Position2dDto from an array, dropping the z axis', function () {
    $dto = Position2dDto::fromArray(['x' => 4.0, 'y' => 5.0, 'z' => 6.0]);

    expect($dto)->not->toBeNull()
        ->and($dto->x)->toBe(4.0)
        ->and($dto->y)->toBe(5.0);
});

it('returns null when Position2dDto is built from null', function () {
    expect(Position2dDto::fromArray(null))->toBeNull();
});

it('maps SDE keys onto an IconDto', function () {
    $dto = IconDto::fromArray(['_key' => 42, 'iconFile' => 'res:/ui/icon.png']);

    expect($dto->id)->toBe(42)
        ->and($dto->file)->toBe('res:/ui/icon.png');
});
