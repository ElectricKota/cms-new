<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

final class SettingsPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $settings = $this->gateway->table('project_settings')->get(1);
        $form = $this->getComponent('settingsForm');
        if ($form instanceof Form) {
            $form->setDefaults((array) ($settings?->toArray() ?? []));
        }
    }

    protected function createComponentSettingsForm(): Form
    {
        $form = new Form();
        foreach (
            [
            'company_name' => 'Název společnosti',
            'person_name' => 'Jméno a příjmení',
            'company_id' => 'IČO',
            'phone' => 'Telefon',
            'email' => 'E-mail',
            'street' => 'Ulice',
            'city' => 'Město',
            'zip' => 'PSČ',
            'facebook_url' => 'Facebook',
            'instagram_url' => 'Instagram',
            'linkedin_url' => 'LinkedIn',
            ] as $name => $label
        ) {
            $form->addText($name, $label);
        }

        foreach (
            [
            'description' => 'Popis webu/společnosti',
            'tracking_head' => 'Měřicí kódy v head',
            'tracking_body' => 'Měřicí kódy v body',
            'intro_text' => 'Úvod',
            'gdpr_text' => 'GDPR',
            'contact_text' => 'Kontakty',
            'terms_text' => 'Obchodní podmínky',
            'product_detail_text' => 'Detail produktu',
            ] as $name => $label
        ) {
            $form->addTextArea($name, $label)->setHtmlAttribute('data-controller', 'tinymce');
        }

        $form->addInteger('og_image_id', 'OG obrázek z galerie');
        $form->addSubmit('send', 'Uložit');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $values['updated_at'] = new \DateTimeImmutable();
            if ($this->gateway->table('project_settings')->get(1)) {
                $this->gateway->update('project_settings', 1, $values);
            } else {
                $values['id'] = 1;
                $this->gateway->insert('project_settings', $values);
            }
            $this->flashMessage('Nastavení uloženo.');
            $this->redirect('this');
        };
        return $form;
    }
}
