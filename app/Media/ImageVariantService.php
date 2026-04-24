<?php

declare(strict_types=1);

namespace App\Media;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Html;
use RuntimeException;

final readonly class ImageVariantService
{
    public function __construct(
        private string $uploadsDir,
        private Explorer $database,
    ) {
    }

    /** @param array{width?:int,height?:int|null,alt?:string,class?:string,loading?:string} $options */
    public function picture(int|string|ActiveRow|null $image, array $options): Html
    {
        $asset = $image instanceof ActiveRow ? $image : $this->findAsset($image);
        if ($asset === null) {
            return Html::el('span', ['hidden' => true]);
        }

        $width = max(1, (int) ($options['width'] ?? 1200));
        $requestedHeight = $options['height'] ?? null;
        $height = $requestedHeight !== null ? max(1, $requestedHeight) : null;
        $variant = $this->variant($asset, $width, $height);

        return Html::el('picture')
            ->addHtml(Html::el('source', [
                'srcset' => $variant['url'],
                'type' => 'image/' . $variant['format'],
            ]))
            ->addHtml(Html::el('img', [
                'src' => $variant['url'],
                'width' => $width,
                'height' => $height,
                'alt' => $options['alt'] ?? (string) ($asset['alt'] ?? ''),
                'class' => $options['class'] ?? null,
                'loading' => $options['loading'] ?? 'lazy',
                'decoding' => 'async',
            ]));
    }

    /** @return array{url:string,format:string} */
    public function variant(ActiveRow $asset, int $width, ?int $height = null): array
    {
        $format = $this->supportsAvif() ? 'avif' : 'webp';
        $hash = sha1(
            $asset['id'] . ':' . $asset['checksum'] . ':' . $width . ':' . ($height ?? 'auto') . ':' . $format
        );
        $relativePath = sprintf('cache/%s.%s', $hash, $format);
        $targetPath = $this->uploadsDir . '/' . $relativePath;

        if (!is_file($targetPath)) {
            $this->createVariant(
                $this->uploadsDir . '/originals/' . $asset['stored_name'],
                $targetPath,
                $format,
                $width,
                $height
            );
            $this->database->table('media_variants')->insert([
                'media_asset_id' => $asset['id'],
                'format' => $format,
                'width' => $width,
                'height' => $height,
                'path' => $relativePath,
                'created_at' => new \DateTimeImmutable(),
            ]);
        }

        return ['url' => '/uploads/' . $relativePath, 'format' => $format];
    }

    private function findAsset(int|string|ActiveRow|null $image): ?ActiveRow
    {
        if ($image === null || $image === '') {
            return null;
        }

        return $this->database->table('media_assets')->get((int) $image);
    }

    private function supportsAvif(): bool
    {
        return function_exists('imageavif') && (imagetypes() & IMG_AVIF) === IMG_AVIF;
    }

    private function createVariant(
        string $sourcePath,
        string $targetPath,
        string $format,
        int $width,
        ?int $height
    ): void {
        if (!is_file($sourcePath)) {
            throw new RuntimeException(sprintf('Source image "%s" does not exist.', $sourcePath));
        }

        $info = getimagesize($sourcePath);
        if ($info === false) {
            throw new RuntimeException(sprintf('File "%s" is not a supported image.', $sourcePath));
        }

        [$sourceWidth, $sourceHeight] = $info;
        $source = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            IMAGETYPE_AVIF => function_exists('imagecreatefromavif') ? imagecreatefromavif($sourcePath) : false,
            default => false,
        };

        if ($source === false) {
            throw new RuntimeException('Unsupported image type.');
        }

        $height ??= (int) round($sourceHeight * ($width / $sourceWidth));
        $ratio = max($width / $sourceWidth, $height / $sourceHeight);
        $cropWidth = (int) round($width / $ratio);
        $cropHeight = (int) round($height / $ratio);
        $srcX = (int) max(0, round(($sourceWidth - $cropWidth) / 2));
        $srcY = (int) max(0, round(($sourceHeight - $cropHeight) / 2));

        $target = imagecreatetruecolor($width, $height);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, $srcX, $srcY, $width, $height, $cropWidth, $cropHeight);

        if (!is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0775, true);
        }

        $saved = $format === 'avif'
            ? imageavif($target, $targetPath, 80)
            : imagewebp($target, $targetPath, 80);

        imagedestroy($source);
        imagedestroy($target);

        if ($saved !== true) {
            throw new RuntimeException(sprintf('Cannot write image variant "%s".', $targetPath));
        }
    }
}
