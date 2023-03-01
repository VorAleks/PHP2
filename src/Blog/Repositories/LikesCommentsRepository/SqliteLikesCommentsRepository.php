<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesCommentsRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthorDidLikeAlreadyException;
use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikeCommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikesForCommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\LikeComment;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\UUID;

use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteLikesCommentsRepository implements LikesCommentsRepositoryInterface
{
    public function __construct(
        private PDO $connection,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws AuthorDidLikeAlreadyException
     */
    public function save(LikeComment $like): void
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_comments 
                    WHERE author_uuid = :author_uuid
                    AND comment_uuid = :comment_uuid'
        );
        $statement->execute([
            ':author_uuid' => (string)$like->getAuthor()->uuid(),
            ':comment_uuid' => (string)$like->getComment()->uuid(),
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $query = "
                INSERT INTO likes_comments (
                    uuid,
                    comment_uuid,
                    author_uuid
                ) VALUES (
                    :uuid,
                    :comment_uuid,
                    :author_uuid
                )
            ";
            $statement = $this->connection->prepare($query);
            $newLikeCommentUuid = (string)$like->uuid();
            $statement->execute([
                ':uuid' =>  (string)$like->uuid(),
                ':comment_uuid' => (string)$like->getComment()->uuid(),
                ':author_uuid' => (string)$like->getAuthor()->uuid(),
            ]);
            $this->logger->info("Like for comment created: $newLikeCommentUuid");
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
     * @throws InvalidArgumentException
     * @throws LikeCommentNotFoundException
     * @throws CommentNotFoundException
     * @throws UserNotFoundException
     */
    public function get(UUID $uuid): LikeComment
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_comments WHERE uuid = :uuid'
        );
        $statement->execute([
            ':uuid' => (string)$uuid,
        ]);
        return $this->getLikeComment($statement, $uuid);
    }

    /**
     * @throws InvalidArgumentException
     * @throws LikeCommentNotFoundException
     * @throws CommentNotFoundException
     * @throws UserNotFoundException
     */
    private function getLikeComment(PDOStatement $statement, string $uuid): LikeComment
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->logger->warning("Cannot find like for comment: $uuid");
            throw new LikeCommentNotFoundException(
                "Cannot find like for comment: $uuid"
            );
        }
        // Создаём объект likePost с uuid
        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);
        $commentRepository = new SqliteCommentsRepository($this->connection, $this->logger);
        return new LikeComment(
            new UUID($result['uuid']),
            $commentRepository->get(new UUID($result['comment_uuid'])),
            $userRepository->get(new UUID($result['author_uuid'])),
        );
    }

    /**
     * @throws LikesForCommentNotFoundException
     * @throws InvalidArgumentException
     * @throws CommentNotFoundException
     * @throws UserNotFoundException
     */
    public function getLikesByCommentUuid(UUID $uuid): array
    {
        $likes = [];
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_comments WHERE comment_uuid = :comment_uuid'
        );
        $statement->execute([
            ':comment_uuid' => (string)$uuid,
        ]);
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        if ($results == false) {
            throw new LikesForCommentNotFoundException(
                "Cannot find likes for comment: $uuid"
            );
        }
        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);
        $commentRepository = new SqliteCommentsRepository($this->connection, $this->logger);
        foreach ($results as $result){
            $likes[] = new LikeComment(
                new UUID($result['uuid']),
                $commentRepository->get(new UUID($result['comment_uuid'])),
                $userRepository->get(new UUID($result['author_uuid'])),
            );
        }
        return $likes;
    }
}