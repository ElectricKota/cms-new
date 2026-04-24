<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

final class TrainingsPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->items = $this->gateway->table('trainings')->order('starts_at DESC');
    }

    protected function createComponentTrainingForm(): Form
    {
        $rooms = $this->gateway->table('rooms')->where('active', true)->fetchPairs('id', 'title');
        $form = new Form();
        $form->addSelect('room_id', 'Místnost', $rooms)->setRequired();
        $form->addText('title', 'Název')->setRequired();
        $form->addText('starts_at', 'Začátek')->setHtmlType('datetime-local')->setRequired();
        $form->addText('ends_at', 'Konec')->setHtmlType('datetime-local')->setRequired();
        $form->addInteger('capacity', 'Kapacita')->setNullable();
        $form->addTextArea('description', 'Popis');
        $form->addSubmit('send', 'Vytvořit');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $values['trainer_user_id'] = (int) $this->getUser()->getId();
            $values['created_at'] = new \DateTimeImmutable();
            $this->gateway->insert('trainings', $values);
            $this->redirect('default');
        };
        return $form;
    }
}
