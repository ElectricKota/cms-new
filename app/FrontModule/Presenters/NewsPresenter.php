<?php

declare(strict_types=1);

namespace App\FrontModule\Presenters;

final class NewsPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->items = $this->gateway->table('content_entries')
            ->where('type', 'news')->where('published', true)->order('created_at DESC');
    }

    public function renderDetail(string $slug): void
    {
        $item = $this->gateway->table('content_entries')
            ->where('type', 'news')->where('slug', $slug)->where('published', true)->fetch();
        if ($item === null) {
            $this->error('Novinka nenalezena.');
        }
        $this->template->item = $item;
    }
}
