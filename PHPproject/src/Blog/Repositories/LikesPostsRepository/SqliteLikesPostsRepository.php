<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesPostsRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthorDidLikeAlreadyException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikePostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikesForPostNotFoundException;
use GeekBrains\LevelTwo\Blog\LikePost;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteLikesPostsRepository implements LikesPostsRepositoryInterface
{
    public function __construct(
        private PDO $connection,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws AuthorDidLikeAlreadyException
     */
    public function save(LikePost $like): void
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_posts WHERE author_uuid = :author_uuid'
        );
        $statement->execute([
            ':author_uuid' => (string)$like->getAuthor()->uuid(),
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $statement = $this->connection->prepare(
                'INSERT INTO likes_posts (uuid, post_uuid, author_uuid)
        VALUES (:uuid, :post_uuid, :author_uuid)'
            );
            $newLikePostUuid = (string)$like->uuid();
            $statement->execute([
                ':uuid' =>  (string)$like->uuid(),
                ':post_uuid' => (string)$like->getPost()->uuid(),
                ':author_uuid' => (string)$like->getAuthor()->uuid(),
            ]);
            $this->logger->info("Like for post created: $newLikePostUuid");
        }
        else {
            $likeAuthor = $like->getAuthor()->name()->first() . ' '
                . $like->getAuthor()->name()->last();
            throw new AuthorDidLikeAlreadyException(
            "Like exist already from author: $likeAuthor"
            );
        }
    }

    /**
     * @throws LikesForPostNotFoundException
     * @throws \GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException
     * @throws LikePostNotFoundException
     */
    public function get(UUID $uuid): LikePost
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_posts WHERE uuid = :uuid'
        );
        $statement->execute([
            ':uuid' => (string)$uuid,
        ]);
        return $this->getLikePost($statement, $uuid);
    }

    /**
     * @throws LikePostNotFoundException
     * @throws \GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException
     */
    private function getLikePost(PDOStatement $statement, string $uuid): LikePost
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->logger->warning("Cannot find like for post: ");
            throw new LikePostNotFoundException(
                "Cannot find like for post: $uuid"
            );
        }
        // Создаём объект likePost с uuid
        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);
        $postRepository = new SqlitePostsRepository($this->connection, $this->logger);
        return new LikePost(
            new UUID($result['uuid']),
            $postRepository->get(new UUID($result['post_uuid'])),
            $userRepository->get(new UUID($result['author_uuid'])),
        );
    }

    /**
     * @throws LikesForPostNotFoundException
     */
    public function getLikesByPostUuid(UUID $postUuid): array
    {
        $likes = [];
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_posts WHERE post_uuid = :post_uuid'
        );
        $statement->execute([
            ':post_uuid' => (string)$postUuid,
        ]);
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        if ($results == false) {
            throw new LikesForPostNotFoundException(
                "Cannot find likes for post: $postUuid"
            );
        }
        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);
        $postRepository = new SqlitePostsRepository($this->connection, $this->logger);
        foreach ($results as $result){
            $likes[] = new LikePost(
                new UUID($result['uuid']),
                $postRepository->get(new UUID($result['post_uuid'])),
                $userRepository->get(new UUID($result['author_uuid'])),
            );
        }
        return $likes;
    }
}