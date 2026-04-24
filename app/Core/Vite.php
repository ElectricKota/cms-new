<?php

declare(strict_types=1);

namespace App\Core;

use Nette\Utils\Html;

final readonly class Vite
{
    public function __construct(
        private string $wwwDir,
        private string $devServer = 'http://127.0.0.1:5173',
    ) {
    }

    public function tags(string $entry): string
    {
        $manifestPath = $this->wwwDir . '/assets/.vite/manifest.json';
        if (!is_file($manifestPath)) {
            return (string) Html::el('script', [
                'type' => 'module',
                'src' => $this->devServer . '/' . ltrim($entry, '/'),
            ]);
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        $asset = $manifest[$entry] ?? null;
        if (!is_array($asset) || !isset($asset['file'])) {
            return '';
        }

        $html = '';
        foreach ($asset['css'] ?? [] as $css) {
            $html .= Html::el('link', ['rel' => 'stylesheet', 'href' => '/assets/' . $css]);
        }

        $html .= Html::el('script', ['type' => 'module', 'src' => '/assets/' . $asset['file']]);
        return $html;
    }
}
