<?php

declare(strict_types=1);

namespace App\FrontModule\Presenters;

final class ProductsPresenter extends BasePresenter
{
    public function renderDefault(?string $tag = null): void
    {
        $selection = $this->gateway->table('products')->where('published', true)->order('created_at DESC');
        if ($tag !== null) {
            $selection->where('tag_names LIKE ?', '%' . $tag . '%');
        }
        $this->template->items = $selection;
    }

    public function renderDetail(string $slug): void
    {
        $product = $this->gateway->table('products')->where('slug', $slug)->where('published', true)->fetch();
        if ($product === null) {
            $this->error('Produkt nenalezen.');
        }
        $this->template->product = $product;
    }
}
