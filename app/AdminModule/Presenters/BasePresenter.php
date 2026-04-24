<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Core\Vite;
use App\Database\TableGateway;
use App\Security\DebugBypassAuthorizator;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Security\User;
use Nette\Utils\Strings;

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
            'Users:default' => 'Uživatelé',
            'Menu:default' => 'Menu',
        ];
        $identity = $this->securityUser->getIdentity();
        $this->template->currentUser = $identity;
        $this->template->currentUserName = $identity?->getData()['name'] ?? 'Nepřihlášený uživatel';
        $this->template->currentUserEmail = $identity?->getData()['email'] ?? null;
        $this->template->currentUserRoles = $identity?->getRoles() ?? [];
    }

    protected function requireRole(string ...$roles): void
    {
        $roles = $roles !== [] ? $roles : ['admin', 'manager'];
        foreach ($roles as $role) {
            if ($this->securityUser->isInRole($role)) {
                return;
            }
        }

        if (!$this->securityUser->isLoggedIn()) {
            $this->redirect('Sign:in');
        } else {
            $this->error('Nemáte oprávnění.', 403);
        }
    }

    protected function uniqueSlug(string $table, string $title, ?int $exceptId = null): string
    {
        $base = Strings::webalize($title);
        $slug = $base !== '' ? $base : 'polozka';
        $suffix = 2;

        while (true) {
            $selection = $this->gateway->table($table)->where('slug', $slug);
            if ($exceptId !== null) {
                $selection->where('id != ?', $exceptId);
            }

            if ($selection->count('*') === 0) {
                return $slug;
            }

            $slug = $base . '-' . $suffix;
            $suffix++;
        }
    }
}
