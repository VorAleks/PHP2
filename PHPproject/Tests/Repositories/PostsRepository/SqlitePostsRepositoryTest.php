<?php

namespace GeekBrains\LevelTwo\Blog\UnitTests\Repositories\PostsRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\UnitTests\DummyLogger;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Person\Name;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqlitePostsRepositoryTest extends TestCase
{
        // Тест, проверяющий, что репозиторий сохраняет данные в БД
        public function testItSavesPostToDatabase(): void
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
            ':uuid' => 'd550d5d7-4ad2-4ee9-8709-19b8a556b66d',
            ':author_uuid' => '9fd67cb4-ea95-4f32-aa23-e6686928ce5e',
            ':title' => 'title',
            ':text' => 'text',
            ]);
            // 4. При вызове метода prepare стаб подключения
            // возвращает мок запроса
            $connectionStub->method('prepare')->willReturn($statementMock);
            // 5. Передаём в репозиторий стаб подключения
            $repository = new SqlitePostsRepository($connectionStub, new DummyLogger());
            // 6. Вызываем метод сохранения пользователя
            $repository->save(
            new Post ( // Свойства пользователя точно такие,
            // как и в описании мока
            new UUID('d550d5d7-4ad2-4ee9-8709-19b8a556b66d'),
            new User(
                new UUID('9fd67cb4-ea95-4f32-aa23-e6686928ce5e'),
                'user',
                new Name('John', 'Smith')
            ),
            'title',
            'text'
            )
            );
        }

        public function testItGetPostByUuid(): void
        {
            $connectionStub = $this->createStub(PDO::class);
            $statementStub = $this->createStub(PDOStatement::class);

            $statementStub->method('fetch')->willReturn([
                'uuid' => 'd550d5d7-4ad2-4ee9-8709-19b8a556b66d',
                'author_uuid' => '9fd67cb4-ea95-4f32-aa23-e6686928ce5e',
                'title' => 'title',
                'text' => 'text',
                'username' => 'user',
                'first_name' => 'first',
                'last_name' => 'last',
            ] );

            $connectionStub->method('prepare')->willReturn($statementStub);
           
            $postRepository = new SqlitePostsRepository($connectionStub, new DummyLogger());

            $post = $postRepository->get(new UUID('d550d5d7-4ad2-4ee9-8709-19b8a556b66d'));

            $this->assertSame('d550d5d7-4ad2-4ee9-8709-19b8a556b66d', (string)$post->uuid());
        } 

        public function testItThrowsAnExceptionWhenThePostNotFound(): void
        {
            $connectionStub = $this->createStub(PDO::class);
            $statementStub = $this->createStub(PDOStatement::class);

            $statementStub->method('fetch')->willReturn(false);
            $connectionStub->method('prepare')->willReturn($statementStub);

            $repository = new SqlitePostsRepository($connectionStub, new DummyLogger());

            $this->expectException(PostNotFoundException::class);
            $this->expectExceptionMessage('Cannot find post: b7276e35-4280-421f-9fef-f5251f89b8ad');

            $repository->get(new UUID('b7276e35-4280-421f-9fef-f5251f89b8ad'));
        }
}