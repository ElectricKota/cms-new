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
        $this->template->galleryItems = $this->galleryItems();
    }

    public function actionDelete(int $id): void
    {
        $this->gateway->delete('galleries', $id);
        $this->redirect('default');
    }

    public function actionDeleteAsset(int $id): void
    {
        $this->gateway->delete('media_assets', $id);
        $this->flashMessage('Fotka smazána z knihovny.');
        $this->redirect('default');
    }

    public function actionDeleteItem(int $id): void
    {
        $this->gateway->delete('gallery_items', $id);
        $this->flashMessage('Fotka odebrána z galerie.');
        $this->redirect('default');
    }

    public function actionMoveItem(int $id, string $direction): void
    {
        $item = $this->gateway->find('gallery_items', $id);
        if ($item === null || !in_array($direction, ['up', 'down'], true)) {
            $this->redirect('default');
        }

        $items = $this->gateway->table('gallery_items')
            ->where('gallery_id', $item['gallery_id'])
            ->order('position, id')
            ->fetchAll();

        foreach ($items as $index => $row) {
            $row->update(['position' => ($index + 1) * 10]);
        }

        $ids = array_map(static fn ($row): int => (int) $row['id'], $items);
        $currentIndex = array_search((int) $id, $ids, true);
        if ($currentIndex === false) {
            $this->redirect('default');
        }

        $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;
        if (!isset($items[$targetIndex])) {
            $this->redirect('default');
        }

        $current = $items[$currentIndex];
        $target = $items[$targetIndex];
        $currentPosition = $current['position'];
        $current->update(['position' => $target['position']]);
        $target->update(['position' => $currentPosition]);
        $this->redirect('default');
    }

    /** @return list<array{id:int,galleryTitle:string,assetId:int,assetName:string,assetAlt:string|null,isFirst:bool,isLast:bool}> */
    private function galleryItems(): array
    {
        $items = [];
        $rows = $this->gateway->table('gallery_items')->order('gallery.title, position, id')->fetchAll();
        $counts = [];

        foreach ($rows as $row) {
            $galleryId = (int) $row['gallery_id'];
            $counts[$galleryId] = ($counts[$galleryId] ?? 0) + 1;
        }

        $positions = [];
        foreach ($rows as $item) {
            $gallery = $item->ref('galleries', 'gallery_id');
            $asset = $item->ref('media_assets', 'media_asset_id');
            if ($gallery === null || $asset === null) {
                continue;
            }

            $galleryId = (int) $item['gallery_id'];
            $positions[$galleryId] = ($positions[$galleryId] ?? 0) + 1;

            $items[] = [
                'id' => (int) $item['id'],
                'galleryTitle' => (string) $gallery['title'],
                'assetId' => (int) $asset['id'],
                'assetName' => (string) $asset['original_name'],
                'assetAlt' => $asset['alt'] !== null ? (string) $asset['alt'] : null,
                'isFirst' => $positions[$galleryId] === 1,
                'isLast' => $positions[$galleryId] === $counts[$galleryId],
            ];
        }

        return $items;
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
