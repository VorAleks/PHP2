<?php

namespace GeekBrains\LevelTwo;

use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
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
        $repository = new SqliteUsersRepository($connectionMock);
        // Ожидаем, что будет брошено исключение
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Cannot find user: Ivan');
        // Вызываем метод получения пользователя
        $repository->getByUsername('Ivan');
    }

    // Тест, проверяющий, что репозиторий сохраняет данные в БД
    public function testItSavesUserToDatabase(): void
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
        ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
        ':username' => 'ivan123',
        ':first_name' => 'Ivan',
        ':last_name' => 'Nikitin',
        ]);
        // 4. При вызове метода prepare стаб подключения
        // возвращает мок запроса
        $connectionStub->method('prepare')->willReturn($statementMock);
        // 5. Передаём в репозиторий стаб подключения
        $repository = new SqliteUsersRepository($connectionStub);
        // 6. Вызываем метод сохранения пользователя
        $repository->save(
        new User( // Свойства пользователя точно такие,
        // как и в описании мока
        new UUID('123e4567-e89b-12d3-a456-426614174000'),
        'ivan123',
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
                'first_name' => 'first',
                'last_name' => 'last'
            ]);

            $connectionStub->method('prepare')->willReturn($statementStub);
           
            $userRepository = new SqliteUsersRepository($connectionStub);

            $user = $userRepository->get(new UUID('9fd67cb4-ea95-4f32-aa23-e6686928ce5e'));

            $this->assertSame('9fd67cb4-ea95-4f32-aa23-e6686928ce5e', (string)$user->uuid());
        } 
}
