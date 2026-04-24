<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

final class PricingPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->items = $this->gateway->table('price_items')->order('category, position, title');
    }

    public function actionDelete(int $id): void
    {
        $this->gateway->delete('price_items', $id);
        $this->redirect('default');
    }

    protected function createComponentPriceForm(): Form
    {
        $form = new Form();
        $form->addText('title', 'Název')->setRequired();
        $form->addTextArea('description', 'Popisek');
        $form->addText('category', 'Kategorie');
        $form->addText('note', 'Poznámka');
        $form->addText('price', 'Cena')->setRequired();
        $form->addInteger('position', 'Pořadí')->setDefaultValue(100);
        $form->addSubmit('send', 'Přidat');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $values['created_at'] = new \DateTimeImmutable();
            $this->gateway->insert('price_items', $values);
            $this->redirect('default');
        };
        return $form;
    }
}
