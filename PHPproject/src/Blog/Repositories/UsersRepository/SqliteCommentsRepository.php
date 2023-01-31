<?php

namespace App\Blog\Repositories\UsersRepository;

use App\Blog\Comment;
use App\Blog\UUID;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\Repositories\UsersRepository\CommentsRepositoryInterface;
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

    // public function getByUsername(string $username): User
    // {
    //     $statement = $this->connection->prepare(
    //     'SELECT * FROM users WHERE username = :username'
    //     );
    //     $statement->execute([
    //     ':username' => $username,
    //     ]);
    // return $this->getUser($statement, $username);
    // }

    // Вынесли общую логику в отдельный приватный метод

    private function getComment(PDOStatement $statement, string $uuid): Comment
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            throw new PostNotFoundException(
            "Cannot find post: $uuid"
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