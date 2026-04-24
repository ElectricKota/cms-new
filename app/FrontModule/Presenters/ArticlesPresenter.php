<?php

declare(strict_types=1);

namespace App\FrontModule\Presenters;

final class ArticlesPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->items = $this->gateway->table('content_entries')
            ->where('type', 'article')->where('published', true)->order('created_at DESC');
    }

    public function renderDetail(string $slug): void
    {
        $item = $this->gateway->table('content_entries')
            ->where('type', 'article')->where('slug', $slug)->where('published', true)->fetch();
        if ($item === null) {
            $this->error('Článek nenalezen.');
        }
        $this->template->item = $item;
    }
}
