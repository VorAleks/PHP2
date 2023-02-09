<?php

namespace GeekBrains\LevelTwo\Blog\UnitTests\Commands;

use GeekBrains\LevelTwo\Blog\Commands\Arguments;
use GeekBrains\LevelTwo\Blog\Commands\CreateUserCommand;
use GeekBrains\LevelTwo\Blog\Exceptions\ArgumentsException;
use GeekBrains\LevelTwo\Blog\Exceptions\CommandException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\DummyUsersRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use PHPUnit\Framework\TestCase;

class CreateUserCommandTest extends TestCase
{
    // Проверяем, что команда создания пользователя бросает исключение,
    // если пользователь с таким именем уже существует
    public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
    {
    // Создаём объект команды
    // У команды одна зависимость - UsersRepositoryInterface        
    $command = new CreateUserCommand(
    // здесь должна быть реализация UsersRepositoryInterface
    new DummyUsersRepository()
    );
    // Описываем тип ожидаемого исключения
    $this->expectException(CommandException::class);
    // и его сообщение
    $this->expectExceptionMessage('User already exists: Ivan');
    // Запускаем команду с аргументами
    $command->handle(new Arguments(['username' => 'Ivan']));
    }

    // Функция возвращает объект типа UsersRepositoryInterface
    private function makeUsersRepository(): UsersRepositoryInterface
    {
    return new class implements UsersRepositoryInterface {
        public function save(User $user): void
        {
        }
        public function get(UUID $uuid): User
        {
        throw new UserNotFoundException("Not found");
        }
        public function getByUsername(string $username): User
        {
        throw new UserNotFoundException("Not found");
        }
        };
    }

    // Тест проверяет, что команда действительно требует фамилию пользователя
    public function testItRequiresLastName(): void
    {
    // Передаём в конструктор команды объект, возвращаемый нашей функцией
    $command = new CreateUserCommand($this->makeUsersRepository());
    $this->expectException(ArgumentsException::class);
    $this->expectExceptionMessage('No such argument: last_name');
    $command->handle(new Arguments([
    'username' => 'Ivan',
    // Нам нужно передать имя пользователя,
    // чтобы дойти до проверки наличия фамилии
    'first_name' => 'Ivan',
    ]));
    }
    // Тест проверяет, что команда действительно требует имя пользователя
    public function testItRequiresFirstName(): void 
    {
    // Вызываем ту же функцию
    $command = new CreateUserCommand($this->makeUsersRepository());
    $this->expectException(ArgumentsException::class);
    $this->expectExceptionMessage('No such argument: first_name');
    $command->handle(new Arguments(['username' => 'Ivan']));
    }

    // Тест, проверяющий, что команда сохраняет пользователя в репозитории
    public function testItSavesUserToRepository(): void
    {
    // Создаём объект анонимного класса
    $usersRepository = new class implements UsersRepositoryInterface {
    // В этом свойстве мы храним информацию о том,
    // был ли вызван метод save
    private bool $called = false;
    public function save(User $user): void
    {
    // Запоминаем, что метод save был вызван
    $this->called = true;
    }
    public function get(UUID $uuid): User
    {
    throw new UserNotFoundException("Not found");
    }
    public function getByUsername(string $username): User
    {
    throw new UserNotFoundException("Not found");
    }
    // Этого метода нет в контракте UsersRepositoryInterface,
    // но ничто не мешает его добавить.
    // С помощью этого метода мы можем узнать,
    // был ли вызван метод save
    public function wasCalled(): bool
    {
    return $this->called;
    }
    };
    // Передаём наш мок в команду
    $command = new CreateUserCommand($usersRepository);
    // Запускаем команду
    $command->handle(new Arguments([
    'username' => 'Ivan',
    'first_name' => 'Ivan',
    'last_name' => 'Nikitin',
    ]));
    // Проверяем утверждение относительно мока,
    // а не утверждение относительно команды
    $this->assertTrue($usersRepository->wasCalled());
    }

}
