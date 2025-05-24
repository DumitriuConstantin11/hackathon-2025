<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use DateTimeImmutable;
use Exception;
use PDO;

class PdoUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {}

    /**
     * @throws Exception
     */
    public function find(mixed $id): ?User
    {
        $query = 'SELECT * FROM users WHERE id = :id';
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        $data = $statement->fetch();
        if (false === $data) {
            return null;
        }

        return new User(
            $data['id'],
            $data['username'],
            $data['password_hash'],
            new DateTimeImmutable($data['created_at']),
        );
    }

    public function findByUsername(string $username): ?User
    {
        // TODO: Implement findByUsername() method.

        $query = 'SELECT * FROM users WHERE username = :username';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['username' => $username]);
        $data = $stmt->fetch();
        if($data===false)
            return null;

        return new User(
            $data['id'],
            $data['username'],
            $data['password_hash'],
            new DateTimeImmutable($data['created_at']),
        );


    }

    public function save(User $user): void
    {
        // TODO: Implement save() method.

        $query= "INSERT INTO users (username, password_hash, created_at) VALUES (?,?,?)";
        $params=[
            $user->username,
            $user->passwordHash,
            $user->createdAt->format(DATE_ATOM),
         ];
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
    }
}
