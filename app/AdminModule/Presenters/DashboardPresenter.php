<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

final class DashboardPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->counts = [
            'Novinky' => $this->gateway->table('content_entries')->where('type', 'news')->count('*'),
            'Články' => $this->gateway->table('content_entries')->where('type', 'article')->count('*'),
            'Produkty' => $this->gateway->table('products')->count('*'),
            'Galerie' => $this->gateway->table('galleries')->count('*'),
            'Tréninky' => $this->gateway->table('trainings')->count('*'),
        ];
    }
}
