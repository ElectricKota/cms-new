<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Security\Passwords;

final class UsersPresenter extends BasePresenter
{
    private const ROLES = [
        'admin' => 'Admin',
        'manager' => 'Uživatel',
        'trainer' => 'Trenér',
        'client' => 'Klient',
    ];

    public function injectPasswords(Passwords $passwords): void
    {
        $this->passwords = $passwords;
    }

    private Passwords $passwords;

    protected function startup(): void
    {
        parent::startup();
        $this->requireRole('admin');
    }

    public function renderDefault(): void
    {
        $this->template->items = $this->gateway->table('users')->order('role, name');
        $this->template->roleLabels = self::ROLES;
    }

    public function actionEdit(?int $id = null): void
    {
        if ($id === null) {
            return;
        }

        $user = $this->gateway->find('users', $id);
        if ($user === null) {
            $this->error('Uživatel nenalezen.');
        }

        $form = $this->getComponent('userForm');
        if ($form instanceof Form) {
            $form->setDefaults($user->toArray());
        }
    }

    public function actionDelete(int $id): void
    {
        if ($this->getUser()->getId() === $id) {
            $this->flashMessage('Aktuálně přihlášeného uživatele nejde smazat.');
            $this->redirect('default');
        }

        $this->gateway->delete('users', $id);
        $this->flashMessage('Uživatel smazán.');
        $this->redirect('default');
    }

    protected function createComponentUserForm(): Form
    {
        $form = new Form();
        $form->addText('name', 'Jméno')->setRequired('Vyplňte jméno.');
        $form->addEmail('email', 'E-mail')->setRequired('Vyplňte e-mail.');
        $form->addText('phone', 'Telefon');
        $form->addSelect('role', 'Role', self::ROLES)->setRequired('Vyberte roli.');
        $form->addPassword('password', 'Heslo')
            ->setOption('description', 'U nového uživatele povinné, při editaci vyplnit jen pro změnu.');
        $form->addCheckbox('active', 'Aktivní')->setDefaultValue(true);
        $form->addSubmit('send', 'Uložit uživatele');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $id = $this->getParameter('id');
            $password = (string) ($values['password'] ?? '');
            unset($values['password']);

            if ($id === null && $password === '') {
                $form->addError('U nového uživatele vyplňte heslo.');
                return;
            }

            if ($password !== '') {
                $values['password_hash'] = $this->passwords->hash($password);
            }

            try {
                if ($id !== null) {
                    $this->gateway->update('users', (int) $id, $values);
                    $this->flashMessage('Uživatel uložen.');
                } else {
                    $values['created_at'] = new \DateTimeImmutable();
                    $this->gateway->insert('users', $values);
                    $this->flashMessage('Uživatel vytvořen.');
                }
            } catch (UniqueConstraintViolationException) {
                $form->addError('Uživatel s tímto e-mailem už existuje.');
                return;
            }

            $this->redirect('default');
        };

        return $form;
    }
}
