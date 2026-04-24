<?php

declare(strict_types=1);

namespace App\Security;

use Nette\Database\Explorer;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

final readonly class CmsAuthenticator implements Authenticator
{
    public function __construct(
        private Explorer $database,
        private Passwords $passwords,
    ) {
    }

    public function authenticate(string $user, string $password): SimpleIdentity
    {
        $row = $this->database->table('users')->where('email', $user)->fetch();
        if ($row === null || !$row['active']) {
            throw new AuthenticationException('Uživatel neexistuje nebo není aktivní.');
        }

        if (!$this->passwords->verify($password, (string) $row['password_hash'])) {
            throw new AuthenticationException('Neplatné heslo.');
        }

        return new SimpleIdentity((int) $row['id'], (string) $row['role'], [
            'email' => $row['email'],
            'name' => $row['name'],
        ]);
    }
}
