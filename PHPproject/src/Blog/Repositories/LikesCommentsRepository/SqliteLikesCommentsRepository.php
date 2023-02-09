<?php


namespace GeekBrains\LevelTwo\Blog\Repositories\LikesCommentsRepository;


use GeekBrains\LevelTwo\Blog\Exceptions\AuthorDidLikeAlreadyException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikesForCommentNotFoundException;
use GeekBrains\LevelTwo\Blog\LikeComment;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;
use PDOStatement;

class SqliteLikesCommentsRepository implements LikesCommentsRepositoryInterface
{
    public function __construct(
        private PDO $connection
    ) {
    }

    /**
     * @throws AuthorDidLikeAlreadyException
     */
    public function save(LikeComment $comment): void
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_comments WHERE author_uuid = :author_uuid'
        );
        $statement->execute([
            ':author_uuid' => (string)$comment->getAuthor()->uuid(),
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $statement = $this->connection->prepare(
                'INSERT INTO likes_comments (uuid, comment_uuid, author_uuid)
        VALUES (:uuid, :comment_uuid, :author_uuid)'
            );
            $statement->execute([
                ':uuid' =>  (string)$comment->uuid(),
                ':comment_uuid' => (string)$comment->getComment()->uuid(),
                ':author_uuid' => (string)$comment->getAuthor()->uuid(),
            ]);
        }
        else {
            throw new AuthorDidLikeAlreadyException(
                "Like exist already from author: $comment"
            );
        }
    }

    /**
     * @throws LikesForCommentNotFoundException
     * @throws \GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException
     * @throws \GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException
     */
    public function getByCommentUuid(UUID $uuid): array
    {
        $likes = [];
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_comments WHERE comment_uuid = :comment_uuid'
        );
        $statement->execute([
            ':comment_uuid' => (string)$uuid,
        ]);
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        if ($results === false) {
            throw new LikesForCommentNotFoundException(
                "Cannot find likes for comment: $uuid"
            );
        }
        $userRepository = new SqliteUsersRepository($this->connection);
        $commentRepository = new SqliteCommentsRepository($this->connection);
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