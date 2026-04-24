<?php

declare(strict_types=1);

namespace App\FrontModule\Presenters;

use App\Core\Vite;
use App\Database\TableGateway;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
    public function __construct(
        protected readonly TableGateway $gateway,
        protected readonly Vite $vite,
    ) {
        parent::__construct();
    }

    protected function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->vite = $this->vite;
        $this->template->settings = $this->gateway->table('project_settings')->get(1);
        $this->template->menuItems = $this->gateway->table('menu_items')->where('visible', true)->order('position');
    }
}
