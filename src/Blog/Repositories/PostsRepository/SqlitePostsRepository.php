<?php
namespace GeekBrains\LevelTwo\Blog\Repositories\PostsRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;

use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqlitePostsRepository implements PostsRepositoryInterface
{
    public function __construct(
    private PDO $connection,
    private LoggerInterface $logger
    ) {
        
    }

    public function save(Post $post): void
    {
        // Подготавливаем запрос
        $statement = $this->connection->prepare(
        'INSERT INTO posts (uuid, author_uuid, title, text)
        VALUES (:uuid, :author_uuid, :title, :text)'
        );
        $newPostUuid = (string)$post->uuid();
        // Выполняем запрос с конкретными значениями
        $statement->execute([
            // Это работает, потому что класс UUID
            // имеет магический метод __toString(),
            // который вызывается, когда объект
            // приводится к строке с помощью (string)
            ':uuid' =>  (string)$post->uuid(),
            ':author_uuid' => $post->getAuthor()->uuid(),
            ':title' => $post->getTitle(),
            ':text' => $post->getText(),
        ]);
        $this->logger->info("Post created: $newPostUuid");
    }

    // Также добавим метод для получения
    // пользователя по его UUID
    /**
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): Post
    {
        $statement = $this->connection->prepare(
        'SELECT * FROM posts WHERE uuid = :uuid'
        );
        $statement->execute([
        ':uuid' => (string)$uuid,
        ]);
        return $this->getPost($statement, $uuid);
    }

    // Вынесли общую логику в отдельный приватный метод

    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException
     * @throws UserNotFoundException
     */
    private function getPost(PDOStatement $statement, string $uuid): Post
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->logger->warning("Cannot find post: $uuid");
            throw new PostNotFoundException(
            "Cannot find post: $uuid"
            );
        }
        // Создаём объект post с uuid
        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);
       return new Post(
        new UUID($result['uuid']),
        $userRepository->get(new UUID($result['author_uuid'])),
        $result['title'],
        $result['text'],
        );
    }

    public function delete(UUID $uuid): void
    {
        $statement = $this->connection->prepare(
            'DELETE FROM posts WHERE posts.uuid=:uuid'
        );
        $statement->execute([
            ':uuid' => (string)$uuid
        ]);
    }
}