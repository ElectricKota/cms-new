<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

final class RoomsPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->items = $this->gateway->table('rooms')->order('title');
    }

    public function actionDelete(int $id): void
    {
        $this->gateway->delete('rooms', $id);
        $this->redirect('default');
    }

    protected function createComponentRoomForm(): Form
    {
        $form = new Form();
        $form->addText('title', 'Název')->setRequired();
        $form->addInteger('capacity', 'Kapacita')
            ->setRequired()
            ->addRule($form::Min, 'Kapacita musí být alespoň 1.', 1);
        $form->addTextArea('description', 'Popis');
        $form->addCheckbox('active', 'Aktivní')->setDefaultValue(true);
        $form->addSubmit('send', 'Uložit');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $values['created_at'] = new \DateTimeImmutable();
            $this->gateway->insert('rooms', $values);
            $this->redirect('default');
        };
        return $form;
    }
}
