<?php

namespace GeekBrains\LevelTwo\Blog;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Person\Name;

class User
{
    public function __construct(
        private UUID $uuid,
        private string $username,
        // Переименовали поле password
        private string $hashedPassword,
        private Name $name
        ) {
    }

    // Функция для вычисления хеша
    private static function hash(string $password, UUID $uuid): string
    {
        return hash('sha256', (string)$uuid . $password);
    }

    // Функция для проверки предъявленного пароля
    public function checkPassword(string $password): bool
    {
        return $this->hashedPassword === self::hash($password, $this->uuid);
    }

    // Функция для создания нового пользователя
    /**
     * @throws InvalidArgumentException
     */
    public static function createFrom(
        string $username,
        string $password,
        Name $name
    ): self
    {
        $uuid = UUID::random();
        return new self(
            $uuid,
            $username,
            // Передаём сгенерированный UUID
            // в функцию хеширования пароля
            self::hash($password, $uuid),
            $name
        );
     }

    /**
     * Get the value of uuid
     */ 
    public function uuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * Get the value of username
     */ 
    public function username(): string
    {
        return $this->username;
    }

    /**
     * Get the value of hashedPassword
     */
    // Переименовали функцию
    public function hashedPassword(): string
    {
        return $this->hashedPassword;
    }

    /**
     * Get the value of name
     */ 
    public function name(): Name
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return (string)$this->name;
    }
}
