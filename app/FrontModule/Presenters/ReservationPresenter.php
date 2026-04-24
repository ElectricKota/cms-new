<?php

declare(strict_types=1);

namespace App\FrontModule\Presenters;

use Nette\Application\UI\Form;
use Ramsey\Uuid\Uuid;

final class ReservationPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->trainings = $this->gateway->table('trainings')
            ->where('starts_at > ?', new \DateTimeImmutable())
            ->order('starts_at');
    }

    public function renderDetail(string $token): void
    {
        $registration = $this->gateway->table('training_registrations')->where('cancel_token', $token)->fetch();
        if ($registration === null) {
            $this->error('Rezervace nenalezena.');
        }
        $this->template->registration = $registration;
    }

    public function actionCancel(string $token): void
    {
        $registration = $this->gateway->table('training_registrations')->where('cancel_token', $token)->fetch();
        if ($registration !== null) {
            $registration->update(['cancelled_at' => new \DateTimeImmutable()]);
        }
        $this->redirect('default');
    }

    protected function createComponentRegistrationForm(): Form
    {
        $trainings = $this->gateway->table('trainings')
            ->where('starts_at > ?', new \DateTimeImmutable())
            ->fetchPairs('id', 'title');
        $form = new Form();
        $form->addSelect('training_id', 'Trénink', $trainings)->setRequired();
        $form->addText('name', 'Jméno')->setRequired();
        $form->addEmail('email', 'E-mail')->setRequired();
        $form->addText('phone', 'Telefon');
        $form->addSubmit('send', 'Rezervovat');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $values['cancel_token'] = Uuid::uuid7()->toString();
            $values['created_at'] = new \DateTimeImmutable();
            $this->gateway->insert('training_registrations', $values);
            $this->redirect('default');
        };
        return $form;
    }
}
