<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

final class OpeningHoursPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->items = $this->gateway->table('opening_hours')->order('position, id DESC');
    }

    public function actionDelete(int $id): void
    {
        $this->gateway->delete('opening_hours', $id);
        $this->redirect('default');
    }

    protected function createComponentOpeningForm(): Form
    {
        $form = new Form();
        $form->addText('label_from', 'Text od')
            ->setHtmlAttribute('placeholder', 'Pondělí, víkend, svátek')
            ->setRequired();
        $form->addText('label_to', 'Text do')->setHtmlAttribute('placeholder', 'Pátek, neděle');
        $form->addText('time_from', 'Čas od')->setRequired();
        $form->addText('time_to', 'Čas do')->setRequired();
        $form->addText('note', 'Poznámka');
        $form->addInteger('position', 'Pořadí')->setDefaultValue(100);
        $form->addSubmit('send', 'Přidat');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $this->gateway->insert('opening_hours', $values);
            $this->redirect('default');
        };
        return $form;
    }
}
