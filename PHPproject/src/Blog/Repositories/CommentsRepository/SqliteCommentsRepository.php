<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use PDO;
use PDOStatement;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{
    
    public function __construct(
    private PDO $connection
    ) {
    }

    public function save(Comment $comment): void
    {
        // Подготавливаем запрос
        $statement = $this->connection->prepare(
        'INSERT INTO comments (uuid, post_uuid, author_uuid, text)
        VALUES (:uuid, :post_uuid, :author_uuid, :text)'
        );
        // Выполняем запрос с конкретными значениями
        $statement->execute([
            // Это работает, потому что класс UUID
            // имеет магический метод __toString(),
            // который вызывается, когда объект
            // приводится к строке с помощью (string)
            ':uuid' =>  (string)$comment->uuid(),
            ':post_uuid' => (string)$comment->getPost()->uuid(),
            ':author_uuid' => (string)$comment->getAuthor()->uuid(),
            ':text' => $comment->getText(),
        ]);
    }

    // Также добавим метод для получения
    // comment по его UUID
    /**
     * @throws CommentNotFoundException
     * @throws \GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException
     */
    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
        'SELECT * FROM comments WHERE uuid = :uuid'
        );
        $statement->execute([
        ':uuid' => (string)$uuid,
        ]);
        return $this->getComment($statement, $uuid);
    }

    // Вынесли общую логику в отдельный приватный метод

    /**
     * @throws CommentNotFoundException
     * @throws \GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException
     */
    private function getComment(PDOStatement $statement, string $uuid): Comment
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            throw new CommentNotFoundException(
            "Cannot find comment: $uuid"
            );
        }
        // Создаём объект post с uuid
        $userRepository = new SqliteUsersRepository($this->connection);
        $postRepository = new SqlitePostsRepository($this->connection);
        return new Comment(
        new UUID($result['uuid']),
        $postRepository->get(new UUID($result['post_uuid'])),
        $userRepository->get(new UUID($result['author_uuid'])),
        $result['text'],
        );
    }  
}