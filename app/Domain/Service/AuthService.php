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
        // TODO: check that a user with same username does not exist, create new user and persist
        // TODO: make sure password is not stored in plain, and proper PHP functions are used for that
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

        // TODO: here is a sample code to start with
        $user = new User( id: null, username: $username, passwordHash:  $hashedPassword, createdAt: new \DateTimeImmutable());
        $this->users->save($user);

        return $user;
    }

    public function attempt(string $username, string $password): bool
    {
        // TODO: implement this for authenticating the user
        // TODO: make sur ethe user exists and the password matches
        // TODO: don't forget to store in session user data needed afterwards

        return true;
    }
}
