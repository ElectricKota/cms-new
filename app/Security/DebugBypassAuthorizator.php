<?php

declare(strict_types=1);

namespace App\Security;

use Nette\Http\IRequest;
use Nette\Security\SimpleIdentity;
use Nette\Security\User;

final readonly class DebugBypassAuthorizator
{
    /** @param list<string> $allowedIps */
    public function __construct(private array $allowedIps)
    {
    }

    public function tryLogin(User $user, IRequest $request): void
    {
        if ($user->isLoggedIn()) {
            return;
        }

        $hasCookie = $request->getCookie('nette-debug') === 'michal';
        $hasIp = in_array($request->getRemoteAddress(), $this->allowedIps, true);
        if (!$hasCookie && !$hasIp) {
            return;
        }

        $user->login(new SimpleIdentity(0, 'admin', [
            'email' => 'debug@michal.local',
            'name' => 'Michal debug',
        ]));
    }
}
