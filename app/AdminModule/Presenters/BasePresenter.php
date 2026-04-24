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
            ['link' => 'Dashboard:default', 'label' => 'Přehled', 'icon' => 'dashboard'],
            ['link' => 'Settings:default', 'label' => 'Nastavení', 'icon' => 'settings'],
            ['link' => 'Galleries:default', 'label' => 'Galerie', 'icon' => 'gallery'],
            ['link' => 'Content:default', 'label' => 'Obsah', 'icon' => 'content'],
            ['link' => 'Products:default', 'label' => 'Produkty', 'icon' => 'products'],
            ['link' => 'Pricing:default', 'label' => 'Ceník', 'icon' => 'pricing'],
            ['link' => 'OpeningHours:default', 'label' => 'Otevírací doba', 'icon' => 'clock'],
            ['link' => 'Rooms:default', 'label' => 'Místnosti', 'icon' => 'rooms'],
            ['link' => 'Trainings:default', 'label' => 'Tréninky', 'icon' => 'trainings'],
            ['link' => 'Users:default', 'label' => 'Uživatelé', 'icon' => 'users'],
            ['link' => 'Menu:default', 'label' => 'Menu', 'icon' => 'menu'],
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
