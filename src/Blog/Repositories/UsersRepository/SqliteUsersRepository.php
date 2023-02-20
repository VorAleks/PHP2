<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    public function __construct(
    private PDO $connection,
    private LoggerInterface $logger
    ) {
    }

    public function save(User $user): void
    {
        // Добавили поле password в запрос
        $statement = $this->connection->prepare(
            'INSERT INTO users
        (uuid, username, password, first_name, last_name)
        VALUES
        (:uuid, :username, :password, :first_name, :last_name)'
        );
        $newUserUuid = (string)$user->uuid();
        $statement->execute([
            ':uuid' => $newUserUuid,
            ':username' => $user->username(),
            // Значения для поля password
            ':password' => $user->hashedPassword(),
            ':first_name' => $user->name()->first(),
            ':last_name' => $user->name()->last(),
        ]);
        $this->logger->info("User created: $newUserUuid");
    }

    // Также добавим метод для получения
    // пользователя по его UUID
    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): User
    {
        $statement = $this->connection->prepare(
        'SELECT * FROM users WHERE uuid = :uuid'
        );
        $stringUuid = (string)$uuid;
        $statement->execute([
        ':uuid' => $stringUuid,
        ]);
        return $this->getUser($statement, $stringUuid);
    }

    /**
     * @throws InvalidArgumentException
     * @throws UserNotFoundException
     */
    public function getByUsername(string $username): User
    {
        $statement = $this->connection->prepare(
        'SELECT * FROM users WHERE username = :username'
        );
        $statement->execute([
        ':username' => $username,
        ]);
    return $this->getUser($statement, $username);
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    private function getUser(PDOStatement $statement, string $username): User
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->logger->warning("Cannot find user: $username");
            throw new UserNotFoundException(
                "Cannot find user: $username"
            );
        }

        // Создаём объект пользователя с полем password
        return new User(
            new UUID($result['uuid']),
            $result['username'],
            $result['password'],
            new Name($result['first_name'], $result['last_name'])
        );
    }
}

