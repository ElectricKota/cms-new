<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

final class ContentPresenter extends BasePresenter
{
    public function renderDefault(?string $type = null): void
    {
        $selection = $this->gateway->table('content_entries')->order('created_at DESC');
        if ($type !== null) {
            $selection->where('type', $type);
        }
        $this->template->items = $selection;
    }

    public function actionEdit(?int $id = null): void
    {
        if ($id !== null) {
            $form = $this->getComponent('contentForm');
            if ($form instanceof Form) {
                $form->setDefaults((array) $this->gateway->find('content_entries', $id)?->toArray());
            }
        }
    }

    public function actionDelete(int $id): void
    {
        $this->gateway->delete('content_entries', $id);
        $this->redirect('default');
    }

    protected function createComponentContentForm(): Form
    {
        $form = new Form();
        $form->addSelect('type', 'Typ', [
            'news' => 'Novinka',
            'article' => 'Článek',
            'page' => 'Stránka',
        ])->setRequired();
        $form->addText('title', 'Titulek')->setRequired();
        $form->addTextArea('excerpt', 'Perex');
        $form->addTextArea('body', 'Text')
            ->setHtmlAttribute('data-controller', 'tinymce');
        $form->addInteger('image_id', 'ID obrázku');
        $form->addCheckbox('published', 'Publikováno');
        $form->addSubmit('send', 'Uložit');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $id = $this->getParameter('id');
            $values['slug'] = $this->uniqueSlug('content_entries', $values['title'], $id !== null ? (int) $id : null);
            $values['updated_at'] = new \DateTimeImmutable();
            if ($id !== null) {
                $this->gateway->update('content_entries', (int) $id, $values);
            } else {
                $values['created_at'] = new \DateTimeImmutable();
                $this->gateway->insert('content_entries', $values);
            }
            $this->redirect('default');
        };
        return $form;
    }
}
