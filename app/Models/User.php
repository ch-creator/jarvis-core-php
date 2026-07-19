<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findByEmail(string $email): ?array
    {
        return $this->database->fetch(
            'SELECT * FROM users WHERE email = :email LIMIT 1',
            ['email' => $email]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->database->fetch(
            'SELECT * FROM users WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function create(string $name, string $email, string $passwordHash): array
    {
        $now = gmdate('Y-m-d H:i:s');

        $this->database->query(
            'INSERT INTO users (name, email, password, created_at, updated_at) VALUES (:name, :email, :password, :created_at, :updated_at)',
            [
                'name' => $name,
                'email' => $email,
                'password' => $passwordHash,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $user = $this->findById((int) $this->database->lastInsertId());

        if ($user === null) {
            throw new \RuntimeException('Failed to create user.');
        }

        return $user;
    }

    public function publicProfile(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'created_at' => $user['created_at'],
        ];
    }
}
