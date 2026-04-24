<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;

final class AdminFormFactory
{
    public function create(callable $onSuccess): Form
    {
        $form = new Form();
        $form->addProtection();
        $form->onSuccess[] = $onSuccess;
        return $form;
    }
}
