<?php
namespace GeekBrains\LevelTwo\Blog\Repositories\PostsRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostsRepositoryException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use PDO;
use PDOException;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqlitePostsRepository implements PostsRepositoryInterface
{
    public function __construct(
    private PDO $connection,
    private LoggerInterface $logger
    ) {
        
    }

    /**
     * @throws PostsRepositoryException
     */
    public function save(Post $post): void
    {
       $query = "
       INSERT INTO posts (
            uuid,
            author_uuid,
            title,
            text
        ) VALUES (
            :uuid,
            :author_uuid,
            :title,
            :text
        ) ON CONFLICT (uuid) DO UPDATE SET
                title = :title,
                text = :text
       ";
       try {
           $statement = $this->connection->prepare($query);
           $newPostUuid = (string)$post->uuid();
           // Выполняем запрос с конкретными значениями
           $statement->execute([
               ':uuid' =>  (string)$post->uuid(),
               ':author_uuid' => $post->getAuthor()->uuid(),
               ':title' => $post->getTitle(),
               ':text' => $post->getText(),
           ]);
           $this->logger->info("Post created: $newPostUuid");
       } catch (PDOException $e) {
           throw new PostsRepositoryException(
               $e->getMessage(), (int)$e->getCode(), $e
           );
       }
    }

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

    /**
     * @throws PostsRepositoryException
     */
    public function delete(UUID $uuid): void
    {
        try {
            $statement = $this->connection->prepare(
                'DELETE FROM posts WHERE posts.uuid=:uuid'
            );
            $statement->execute([
                ':uuid' => (string)$uuid
            ]);
        } catch (PDOException $e) {
            throw new PostsRepositoryException(
                $e->getMessage(), (int)$e->getCode(), $e
            );
        }
    }
}