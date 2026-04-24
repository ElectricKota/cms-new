<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

final class OpeningHoursPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->items = $this->gateway->table('opening_hours')->order('date_from DESC, time_from');
    }

    public function actionDelete(int $id): void
    {
        $this->gateway->delete('opening_hours', $id);
        $this->redirect('default');
    }

    protected function createComponentOpeningForm(): Form
    {
        $form = new Form();
        $form->addText('date_from', 'Datum od')->setHtmlType('date')->setRequired();
        $form->addText('date_to', 'Datum do')->setHtmlType('date');
        $form->addText('time_from', 'Čas od')->setHtmlType('time')->setRequired();
        $form->addText('time_to', 'Čas do')->setHtmlType('time')->setRequired();
        $form->addText('note', 'Poznámka');
        $form->addSubmit('send', 'Přidat');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $this->gateway->insert('opening_hours', $values);
            $this->redirect('default');
        };
        return $form;
    }
}
