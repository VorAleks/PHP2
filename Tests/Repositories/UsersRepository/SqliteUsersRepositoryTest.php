<?php

namespace GeekBrains\LevelTwo;

use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\UnitTests\DummyLogger;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Person\Name;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqliteUsersRepositoryTest extends TestCase
{
    // Тест, проверяющий, что SQLite-репозиторий бросает исключение,
    // когда запрашиваемый пользователь не найден
    public function testItThrowsAnExceptionWhenUserNotFound(): void
    {
        // Сначала нам нужно подготовить все стабы
        // 2. Создаём стаб подключения
        $connectionMock = $this->createStub(PDO::class);
        // 4. Стаб запроса
        $statementStub = $this->createStub(PDOStatement::class);
        // 5. Стаб запроса будет возвращать false
        // при вызове метода fetch
        $statementStub->method('fetch')->willReturn(false);
        // 3. Стаб подключения будет возвращать другой стаб -
        // стаб запроса - при вызове метода prepare
        $connectionMock->method('prepare')->willReturn($statementStub);
        // 1. Передаём в репозиторий стаб подключения
        $repository = new SqliteUsersRepository($connectionMock, new DummyLogger());
        // Ожидаем, что будет брошено исключение
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Cannot find user: Ivan');
        // Вызываем метод получения пользователя
        $repository->getByUsername('Ivan');
    }

    // Тест, проверяющий, что репозиторий сохраняет данные в БД
    public function testItSavesUserToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ':username' => 'ivan123',
        // добавили пароль
                ':password' => 'some_password',
                ':first_name' => 'Ivan',
                ':last_name' => 'Nikitin',
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);
        $repository = new SqliteUsersRepository($connectionStub, new DummyLogger());
        $repository->save(
            new User(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                'ivan123',
        // добавили пароль
                'some_password',
                new Name('Ivan', 'Nikitin')
            )
        );
    }

    public function testItGetUserByUuid(): void
        {
            $connectionStub = $this->createStub(PDO::class);
            $statementStub = $this->createStub(PDOStatement::class);

            $statementStub->method('fetch')->willReturn([
                'uuid' => '9fd67cb4-ea95-4f32-aa23-e6686928ce5e',
                'username' => 'user',
                'password' => 'pass',
                'first_name' => 'first',
                'last_name' => 'last'
            ]);

            $connectionStub->method('prepare')->willReturn($statementStub);
           
            $userRepository = new SqliteUsersRepository($connectionStub, new DummyLogger());

            $user = $userRepository->get(new UUID('9fd67cb4-ea95-4f32-aa23-e6686928ce5e'));

            $this->assertSame('9fd67cb4-ea95-4f32-aa23-e6686928ce5e', (string)$user->uuid());
        } 
}
