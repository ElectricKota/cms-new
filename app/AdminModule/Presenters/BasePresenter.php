<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Core\Vite;
use App\Database\TableGateway;
use App\Security\DebugBypassAuthorizator;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Security\User;

abstract class BasePresenter extends Presenter
{
    public function __construct(
        protected readonly TableGateway $gateway,
        protected readonly Vite $vite,
        private readonly DebugBypassAuthorizator $debugBypass,
        private readonly IRequest $httpRequest,
        private readonly User $securityUser,
    ) {
        parent::__construct();
    }

    protected function startup(): void
    {
        parent::startup();
        $this->debugBypass->tryLogin($this->securityUser, $this->httpRequest);

        if (!$this->securityUser->isLoggedIn() && $this->getName() !== 'Admin:Sign') {
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }
    }

    protected function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->vite = $this->vite;
        $this->template->adminMenu = [
            'Dashboard:default' => 'Přehled',
            'Settings:default' => 'Nastavení',
            'Galleries:default' => 'Galerie',
            'Content:default' => 'Obsah',
            'Products:default' => 'Produkty',
            'Pricing:default' => 'Ceník',
            'OpeningHours:default' => 'Otevírací doba',
            'Rooms:default' => 'Místnosti',
            'Trainings:default' => 'Tréninky',
            'Menu:default' => 'Menu',
        ];
    }

    protected function requireRole(string ...$roles): void
    {
        if (!$this->securityUser->isInRole('admin') && !$this->securityUser->isInRole('manager')) {
            $this->error('Nemáte oprávnění.', 403);
        }
    }
}
