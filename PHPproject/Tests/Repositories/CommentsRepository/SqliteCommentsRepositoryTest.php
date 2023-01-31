<?php

namespace GeekBrains\LevelTwo\Blog\UnitTests\Repositories\CommentsRepository;

use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Person\Name;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqliteCommentsRepositoryTest extends TestCase
{
    // Тест, проверяющий, что репозиторий сохраняет данные в БД
    public function testItSavesCommentToDatabase(): void
    {
        // 1. Создаём стаб подключения
        $connectionStub = $this->createStub(PDO::class);
        // 2. Создаём мок запроса, возвращаемый стабом подключения
        $statementMock = $this->createMock(PDOStatement::class);
        // 3. Описываем ожидаемое взаимодействие
        // нашего репозитория с моком запроса
        $statementMock
        ->expects($this->once()) // Ожидаем, что будет вызван один раз
        ->method('execute') // метод execute
        ->with([ // с единственным аргументом - массивом
        ':uuid' => '041d76de-15ef-4c56-b0da-c1a2ca644497',
        ':post_uuid' =>'fff81673-d726-49e8-b16e-82113319c06c',
        ':author_uuid' => '14504c0d-c9a8-4f9b-996d-3d567f73bc8d',
        ':text' => 'text',
        ]);
        // 4. При вызове метода prepare стаб подключения
        // возвращает мок запроса
        $connectionStub->method('prepare')->willReturn($statementMock);
        // 5. Передаём в репозиторий стаб подключения
        $repository = new SqliteCommentsRepository($connectionStub);
        // 6. Вызываем метод сохранения пользователя
        $repository->save(
            new Comment ( // Свойства пользователя точно такие,
                // как и в описании мока
                new UUID('041d76de-15ef-4c56-b0da-c1a2ca644497'),
                new Post ( // Свойства пользователя точно такие,
                    // как и в описании мока
                    new UUID('fff81673-d726-49e8-b16e-82113319c06c'),
                    new User(
                        new UUID('9fd67cb4-ea95-4f32-aa23-e6686928ce5e'),
                        'user',
                        new Name('John', 'Smith')
                    ),
                    'title',
                    'text'
                ),
                new User( // Свойства пользователя точно такие,
                    // как и в описании мока
                    new UUID('14504c0d-c9a8-4f9b-996d-3d567f73bc8d'),
                    'ivan123',
                    new Name('Ivan', 'Nikitin')
                ),
                'text'
            )
        );
    }

    public function testItGetCommentByUuid(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetch')->willReturn([
            'uuid' => 'df79ee08-81cc-4068-b7cb-cb445449910d',
            'post_uuid' =>'fff81673-d726-49e8-b16e-82113319c06c',
            'author_uuid' => '14504c0d-c9a8-4f9b-996d-3d567f73bc8d',
            'text' => 'Comment text',
            'author_uuid' =>'9fd67cb4-ea95-4f32-aa23-e6686928ce5e',
            'title' => 'Post title',
            'text' => 'Post text',
            'username' =>'user',
            'first_name' => 'John',
            'last_name' => 'Smith'
        ]);

        $connectionStub->method('prepare')->willReturn($statementStub);
        $commentRepository = new SqliteCommentsRepository($connectionStub);

        $comment = $commentRepository->get(new UUID('df79ee08-81cc-4068-b7cb-cb445449910d'));
        $this->assertSame('df79ee08-81cc-4068-b7cb-cb445449910d', (string)$comment->uuid());
    }

    public function testItThrowsAnExceptionWhenCommentNotFound(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqliteCommentsRepository($connectionStub);

        $this->expectException(CommentNotFoundException::class);
        $this->expectExceptionMessage('Cannot find comment: 123e4567-e89b-12d3-a456-426614174000');

        $repository->get(new UUID('123e4567-e89b-12d3-a456-426614174000'));
    }
}