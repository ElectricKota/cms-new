<?php

declare(strict_types=1);

namespace App\Media;

use Latte\Extension;

final class ImageLatteExtension extends Extension
{
    public function __construct(private readonly ImageVariantService $images)
    {
    }

    public function getFunctions(): array
    {
        return [
            'cmsPicture' => fn (int|string|null $image, ...$options) => $this->images->picture($image, $options),
        ];
    }
}
