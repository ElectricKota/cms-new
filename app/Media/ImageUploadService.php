<?php

declare(strict_types=1);

namespace App\Media;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Http\FileUpload;
use Nette\Utils\Strings;
use Ramsey\Uuid\Uuid;
use RuntimeException;

final readonly class ImageUploadService
{
    public function __construct(
        private string $uploadsDir,
        private Explorer $database,
    ) {
    }

    public function upload(FileUpload $upload, ?string $alt = null): ActiveRow
    {
        if (!$upload->isOk() || !$upload->isImage()) {
            throw new RuntimeException('Nahraný soubor není platný obrázek.');
        }

        $extension = strtolower((string) pathinfo($upload->getSanitizedName(), PATHINFO_EXTENSION));
        $storedName = Uuid::uuid7()->toString() . '.' . ($extension ?: 'jpg');
        $target = $this->uploadsDir . '/originals/' . $storedName;

        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0775, true);
        }

        $upload->move($target);
        $info = getimagesize($target);
        if ($info === false) {
            @unlink($target);
            throw new RuntimeException('Nahraný obrázek nejde načíst.');
        }

        return $this->database->table('media_assets')->insert([
            'original_name' => $upload->getUntrustedName(),
            'stored_name' => $storedName,
            'mime_type' => $upload->getContentType(),
            'size_bytes' => filesize($target),
            'width' => $info[0],
            'height' => $info[1],
            'alt' => $alt !== null ? Strings::trim($alt) : null,
            'checksum' => hash_file('sha256', $target),
            'created_at' => new \DateTimeImmutable(),
        ]);
    }
}
