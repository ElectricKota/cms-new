<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

final class ProductsPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->items = $this->gateway->table('products')->order('created_at DESC');
    }

    public function actionEdit(?int $id = null): void
    {
        if ($id !== null) {
            $form = $this->getComponent('productForm');
            if ($form instanceof Form) {
                $form->setDefaults((array) $this->gateway->find('products', $id)?->toArray());
            }
        }
    }

    public function actionDelete(int $id): void
    {
        $this->gateway->delete('products', $id);
        $this->redirect('default');
    }

    protected function createComponentProductForm(): Form
    {
        $form = new Form();
        $form->addText('title', 'Název')->setRequired();
        $form->addTextArea('description', 'Popisek')->setHtmlAttribute('data-controller', 'tinymce');
        $form->addInteger('main_image_id', 'Hlavní obrázek');
        $form->addText('category_names', 'Kategorie')->setHtmlAttribute('placeholder', 'Oddělit čárkou');
        $form->addText('tag_names', 'Tagy')->setHtmlAttribute('placeholder', 'Oddělit čárkou');
        $form->addCheckbox('published', 'Publikováno');
        $form->addSubmit('send', 'Uložit');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $id = $this->getParameter('id');
            $values['slug'] = $this->uniqueSlug('products', $values['title'], $id !== null ? (int) $id : null);
            $values['updated_at'] = new \DateTimeImmutable();
            if ($id !== null) {
                $this->gateway->update('products', (int) $id, $values);
            } else {
                $values['created_at'] = new \DateTimeImmutable();
                $this->gateway->insert('products', $values);
            }
            $this->redirect('default');
        };
        return $form;
    }
}
