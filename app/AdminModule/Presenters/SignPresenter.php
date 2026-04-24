<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

final class SignPresenter extends BasePresenter
{
    public function actionOut(): void
    {
        $this->getUser()->logout(true);
        $this->redirect('in');
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form();
        $form->addEmail('email', 'E-mail')->setRequired();
        $form->addPassword('password', 'Heslo')->setRequired();
        $form->addSubmit('send', 'Přihlásit');
        $form->onSuccess[] = function (Form $form, array $values): void {
            try {
                $this->getUser()->login($values['email'], $values['password']);
                $this->restoreRequest($this->getParameter('backlink'));
                $this->redirect('Dashboard:default');
            } catch (AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };
        return $form;
    }
}
