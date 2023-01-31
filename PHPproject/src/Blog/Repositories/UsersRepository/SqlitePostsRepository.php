<?php
namespace App\Blog\Repositories\UsersRepository;

use App\Blog\Post;
use App\Blog\UUID;
// use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
// use App\Blog\Repositories\UsersRepository\PostsRepositoryInterface;
use PDO;
use PDOStatement;

class SqlitePostsRepository implements PostsRepositoryInterface
{
    public function __construct(
    private PDO $connection
    ) {
        
    }

    public function save(Post $post): void
    {
        // Подготавливаем запрос
        $statement = $this->connection->prepare(
        'INSERT INTO posts (uuid, author_uuid, title, text)
        VALUES (:uuid, :author_uuid, :title, :text)'
        );
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
    }

    // Также добавим метод для получения
    // пользователя по его UUID
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
    private function getPost(PDOStatement $statement, string $uuid): Post
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            throw new PostNotFoundException(
            "Cannot find post: $uuid"
            );
        }
        // Создаём объект post с uuid
        $userRepository = new SqliteUsersRepository($this->connection);
       return new Post(
        new UUID($result['uuid']),
        $userRepository->get(new UUID($result['author_uuid'])),
        $result['title'],
        $result['text'],
        );
    }  
}