<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    )
    {
    }

    public function register(string $username, string $password): User
    {
        if (strlen($username) < 4) {
            throw new \InvalidArgumentException("Username trebuie sa fie de minim 4 caractere");
        }
        if (preg_match("/^(?=.*\d).{8,}$/", $username)) {
            throw new \InvalidArgumentException("Username trebuie sa fie de minim 8 caractere si sa contina un numar");
        }
        $existingUser = $this->users->findByUsername($username);
        if ($existingUser !== null) {
            throw new \InvalidArgumentException("Usernameul este luat");
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $user = new User( id: null, username: $username, passwordHash:  $hashedPassword, createdAt: new \DateTimeImmutable());
        $this->users->save($user);

        return $user;
    }

    public function attempt(string $username, string $password): bool
    {
        $user = $this->users->findByUsername($username);
        if(!$user) {
            return false;
        }
        if (!password_verify($password, $user->passwordHash)) {
            return false;
        }
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;


        return true;
    }
}
