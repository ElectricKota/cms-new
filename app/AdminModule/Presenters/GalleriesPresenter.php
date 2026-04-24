<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Media\ImageUploadService;
use Nette\Application\UI\Form;

final class GalleriesPresenter extends BasePresenter
{
    public function injectImageUploadService(ImageUploadService $imageUploadService): void
    {
        $this->imageUploadService = $imageUploadService;
    }

    private ImageUploadService $imageUploadService;

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

    public function actionDeleteAsset(int $id): void
    {
        $this->gateway->delete('media_assets', $id);
        $this->redirect('default');
    }

    protected function createComponentGalleryForm(): Form
    {
        $form = new Form();
        $form->addText('title', 'Název galerie')->setRequired();
        $form->addMultiUpload('images', 'Fotky')
            ->setHtmlAttribute('accept', 'image/*')
            ->setHtmlAttribute('capture', 'environment');
        $form->addSubmit('send', 'Vytvořit galerii');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $title = (string) $values['title'];
            $uploads = $values['images'] ?? [];
            unset($values['images']);
            $values['created_at'] = new \DateTimeImmutable();
            $values['slug'] = $this->uniqueSlug('galleries', $title);
            $gallery = $this->gateway->insert('galleries', $values);
            foreach ($uploads as $upload) {
                if ($upload->isOk()) {
                    $asset = $this->imageUploadService->upload($upload);
                    $this->gateway->insert('gallery_items', [
                        'gallery_id' => $gallery['id'],
                        'media_asset_id' => $asset['id'],
                        'created_at' => new \DateTimeImmutable(),
                    ]);
                }
            }
            $this->redirect('default');
        };
        return $form;
    }

    protected function createComponentUploadForm(): Form
    {
        $form = new Form();
        $form->addSelect('gallery_id', 'Galerie', $this->gateway->table('galleries')->fetchPairs('id', 'title'))
            ->setPrompt('Jen nahrát do knihovny');
        $form->addMultiUpload('images', 'Fotky')
            ->setRequired('Vyberte aspoň jednu fotku.')
            ->setHtmlAttribute('accept', 'image/*')
            ->setHtmlAttribute('capture', 'environment');
        $form->addSubmit('send', 'Nahrát fotky');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $uploads = $values['images'] ?? [];
            foreach ($uploads as $upload) {
                $asset = $this->imageUploadService->upload($upload);
                if ($values['gallery_id'] !== null) {
                    $this->gateway->insert('gallery_items', [
                        'gallery_id' => $values['gallery_id'],
                        'media_asset_id' => $asset['id'],
                        'created_at' => new \DateTimeImmutable(),
                    ]);
                }
            }
            $this->flashMessage('Fotky jsou nahrané.');
            $this->redirect('default');
        };
        return $form;
    }
}
