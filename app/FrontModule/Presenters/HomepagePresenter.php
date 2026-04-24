<?php

declare(strict_types=1);

namespace App\FrontModule\Presenters;

final class HomepagePresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->news = $this->gateway->table('content_entries')
            ->where('type', 'news')->where('published', true)->order('created_at DESC')->limit(3);
        $this->template->products = $this->gateway->table('products')
            ->where('published', true)->order('created_at DESC')->limit(6);
        $this->template->pricing = $this->gateway->table('price_items')->order('category, position, title');
        $this->template->openingHours = $this->gateway->table('opening_hours')->order('date_from DESC')->limit(10);
    }

    public function renderPage(string $slug): void
    {
        $page = $this->gateway->table('content_entries')
            ->where('type', 'page')->where('slug', $slug)->where('published', true)->fetch();
        if ($page === null) {
            $this->error('Stránka nenalezena.');
        }
        $this->template->page = $page;
    }
}
