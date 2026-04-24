<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

final class GalleriesPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->items = $this->gateway->table('galleries')->order('title');
        $this->template->media = $this->gateway->table('media_assets')->order('created_at DESC')->limit(60);
    }

    public function actionDelete(int $id): void
    {
        $this->gateway->delete('galleries', $id);
        $this->redirect('default');
    }

    protected function createComponentGalleryForm(): Form
    {
        $form = new Form();
        $form->addText('title', 'Název galerie')->setRequired();
        $form->addText('slug', 'Slug');
        $form->addSubmit('send', 'Vytvořit');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $values['created_at'] = new \DateTimeImmutable();
            $this->gateway->insert('galleries', $values);
            $this->redirect('default');
        };
        return $form;
    }
}
