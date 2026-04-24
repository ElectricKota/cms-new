<?php

declare(strict_types=1);

namespace App\ErrorModule\Presenters;

use Nette\Application\UI\Presenter;

final class ErrorPresenter extends Presenter
{
    public function renderDefault(\Throwable $exception): void
    {
        $this->template->code = $exception->getCode() ?: 500;
    }
}
