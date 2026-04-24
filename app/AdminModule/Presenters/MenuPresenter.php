<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

final class MenuPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->items = $this->gateway->table('menu_items')->order('parent_id, position, title');
    }

    public function actionDelete(int $id): void
    {
        $this->gateway->delete('menu_items', $id);
        $this->redirect('default');
    }

    protected function createComponentMenuForm(): Form
    {
        $form = new Form();
        $form->addText('title', 'Text odkazu')->setRequired();
        $form->addSelect('target_type', 'Typ', [
            'url' => 'URL',
            'anchor' => 'Kotva',
            'news_index' => 'Přehled novinek',
            'article_index' => 'Přehled článků',
            'content_detail' => 'Detail obsahu',
            'product_detail' => 'Detail produktu',
        ])->setRequired();
        $form->addText('target_value', 'Cíl')->setHtmlAttribute('placeholder', '/kontakt, #cenik, slug nebo ID');
        $form->addInteger('parent_id', 'Nadřazená položka')->setNullable();
        $form->addInteger('position', 'Pořadí')->setDefaultValue(100);
        $form->addCheckbox('visible', 'Viditelné')->setDefaultValue(true);
        $form->addSubmit('send', 'Přidat');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $this->gateway->insert('menu_items', $values);
            $this->redirect('default');
        };
        return $form;
    }
}
