<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

final class TrainingsPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->items = $this->gateway->table('trainings')->order('starts_at DESC');
    }

    protected function createComponentTrainingForm(): Form
    {
        $rooms = $this->gateway->table('rooms')->where('active', true)->fetchPairs('id', 'title');
        $form = new Form();
        $form->addSelect('room_id', 'Místnost', $rooms)->setRequired();
        $form->addText('title', 'Název')->setRequired();
        $form->addText('date', 'Datum')->setHtmlType('date')->setRequired();
        $form->addText('time_from', 'Čas od')->setHtmlType('time')->setRequired();
        $form->addText('time_to', 'Čas do')->setHtmlType('time')->setRequired();
        $form->addTextArea('description', 'Popis');
        $form->addSubmit('send', 'Vytvořit');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $room = $this->gateway->find('rooms', (int) $values['room_id']);
            if ($room === null) {
                $form->addError('Vybraná místnost neexistuje.');
                return;
            }

            $startsAt = new \DateTimeImmutable($values['date'] . ' ' . $values['time_from']);
            $endsAt = new \DateTimeImmutable($values['date'] . ' ' . $values['time_to']);
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
                'trainer_user_id' => $this->getUser()->getId() !== null ? (int) $this->getUser()->getId() : null,
                'title' => $values['title'],
                'description' => $values['description'],
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'capacity' => $room['capacity'],
                'created_at' => new \DateTimeImmutable(),
            ]);
            $this->redirect('default');
        };
        return $form;
    }
}
