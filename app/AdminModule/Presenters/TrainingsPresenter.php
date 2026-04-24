<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

final class TrainingsPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $items = [];
        foreach ($this->gateway->table('trainings')->order('starts_at DESC') as $item) {
            $room = $item->ref('rooms', 'room_id');
            $trainer = $item->ref('users', 'trainer_user_id');

            $items[] = [
                'title' => (string) $item['title'],
                'trainerName' => $trainer !== null ? (string) $trainer['name'] : 'Bez trenéra',
                'startsAt' => $item['starts_at'],
                'endsAt' => $item['ends_at'],
                'roomTitle' => $room !== null ? (string) $room['title'] : 'Neznámá místnost',
                'capacity' => $item['capacity'],
            ];
        }

        $this->template->items = $items;
    }

    protected function createComponentTrainingForm(): Form
    {
        $rooms = $this->gateway->table('rooms')->where('active', true)->fetchPairs('id', 'title');
        $trainers = $this->gateway->table('users')
            ->where('role', 'trainer')
            ->where('active', true)
            ->order('name')
            ->fetchPairs('id', 'name');

        $form = new Form();
        $form->addSelect('room_id', 'Místnost', $rooms)->setRequired();
        if ($this->getUser()->isInRole('trainer')) {
            $form->addHidden('trainer_user_id', (string) $this->getUser()->getId());
        } else {
            $form->addSelect('trainer_user_id', 'Trenér', $trainers)
                ->setPrompt('Bez trenéra');
        }

        $form->addText('title', 'Název')->setRequired();
        $form->addInteger('image_id', 'ID fotky')->setNullable();
        $form->addText('date', 'Datum')
            ->setHtmlType('text')
            ->setHtmlAttribute('data-controller', 'datepicker')
            ->setHtmlAttribute('placeholder', 'dd.mm.rrrr')
            ->setRequired();
        $form->addText('time_from', 'Čas od')->setHtmlType('time')->setRequired();
        $form->addText('time_to', 'Čas do')->setHtmlType('time')->setRequired();
        $form->addTextArea('description', 'Popis')
            ->setHtmlAttribute('data-controller', 'tinymce');
        $form->addSubmit('send', 'Vytvořit');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $room = $this->gateway->find('rooms', (int) $values['room_id']);
            if ($room === null) {
                $form->addError('Vybraná místnost neexistuje.');
                return;
            }

            $trainerId = $values['trainer_user_id'] ?? null;
            if ($trainerId !== null && $trainerId !== '') {
                $trainer = $this->gateway->find('users', (int) $trainerId);
                if ($trainer === null || $trainer['role'] !== 'trainer' || !$trainer['active']) {
                    $form->addError('Vybraný trenér neexistuje nebo není aktivní.');
                    return;
                }
            }

            $imageId = $values['image_id'] ?? null;
            if ($imageId !== null && $imageId !== '') {
                $image = $this->gateway->find('media_assets', (int) $imageId);
                if ($image === null) {
                    $form->addError('Vybraná fotka neexistuje v knihovně médií.');
                    return;
                }
            }

            $startsAt = $this->createDateTime((string) $values['date'], (string) $values['time_from']);
            $endsAt = $this->createDateTime((string) $values['date'], (string) $values['time_to']);
            if ($startsAt === null || $endsAt === null) {
                $form->addError('Zadejte platné datum a čas tréninku.');
                return;
            }

            if ($endsAt <= $startsAt) {
                $form->addError('Konec tréninku musí být po začátku.');
                return;
            }

            $overlap = $this->gateway->table('trainings')
                ->where('room_id', $values['room_id'])
                ->where('starts_at < ?', $endsAt)
                ->where('ends_at > ?', $startsAt)
                ->count('*') > 0;
            if ($overlap) {
                $form->addError('Místnost je v tomto čase už obsazená.');
                return;
            }

            $this->gateway->insert('trainings', [
                'room_id' => $values['room_id'],
                'trainer_user_id' => $trainerId !== null && $trainerId !== '' ? (int) $trainerId : null,
                'title' => $values['title'],
                'description' => $values['description'],
                'image_id' => $imageId !== null && $imageId !== '' ? (int) $imageId : null,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'capacity' => $room['capacity'],
                'created_at' => new \DateTimeImmutable(),
            ]);
            $this->redirect('default');
        };
        return $form;
    }

    private function createDateTime(string $date, string $time): ?\DateTimeImmutable
    {
        $date = trim($date);
        $time = trim($time);
        $format = str_contains($date, '.') ? '!d.m.Y H:i' : '!Y-m-d H:i';
        $dateTime = \DateTimeImmutable::createFromFormat($format, $date . ' ' . $time);

        return $dateTime !== false ? $dateTime : null;
    }
}
