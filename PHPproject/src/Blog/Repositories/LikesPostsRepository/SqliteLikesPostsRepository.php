<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesPostsRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthorDidLikeAlreadyException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikesForPostNotFoundException;
use GeekBrains\LevelTwo\Blog\LikePost;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;
use PDOStatement;

class SqliteLikesPostsRepository implements LikesPostsRepositoryInterface
{
    public function __construct(
        private PDO $connection
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
            $statement->execute([
                ':uuid' =>  (string)$like->uuid(),
                ':post_uuid' => (string)$like->getPost()->uuid(),
                ':author_uuid' => (string)$like->getAuthor()->uuid(),
            ]);
        }
        else {
            throw new AuthorDidLikeAlreadyException(
            "Like exist already from author: $like"
            );
        }
    }

    /**
     * @throws LikesForPostNotFoundException
     */
    public function getByPostUuid(UUID $uuid): array
    {
        $likes = [];
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_posts WHERE post_uuid = :post_uuid'
        );
        $statement->execute([
            ':post_uuid' => (string)$uuid,
        ]);
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        if ($results === false) {
            throw new LikesForPostNotFoundException(
                "Cannot find likes for post: $uuid"
            );
        }
        $userRepository = new SqliteUsersRepository($this->connection);
        $postRepository = new SqlitePostsRepository($this->connection);
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