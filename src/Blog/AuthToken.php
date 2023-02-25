<?php

namespace GeekBrains\LevelTwo\Blog;

use DateTimeImmutable;

class AuthToken
{
    public function __construct(
        // Строка токена
        private string $token,
        // UUID пользователя
        private UUID $userUuid,
        // Срок годности
        private DateTimeImmutable $expiresOn
    ) {
    }
    /**
     * Get the value of token
     */
    public function token(): string
    {
        return $this->token;
    }
    /**
     * Get the value of userUuid
     */
    public function userUuid(): UUID
    {
        return $this->userUuid;
    }
    /**
     * Get the value of expiresOn
     */
    public function expiresOn(): DateTimeImmutable
    {
        return $this->expiresOn;
    }

    /**
     * Set the value of expiresOn as current date
     */
    public function expiresOff(): void
    {
        $this->expiresOn = new DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->token;
    }
}