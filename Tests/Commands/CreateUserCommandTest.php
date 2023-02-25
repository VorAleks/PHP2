<?php

namespace GeekBrains\LevelTwo\Blog\UnitTests\Commands;

//use GeekBrains\LevelTwo\Blog\Commands\CreateUserCommand;
use GeekBrains\LevelTwo\Blog\Commands\Users\CreateUser;
//use GeekBrains\LevelTwo\Blog\Exceptions\ArgumentsException;
//use GeekBrains\LevelTwo\Blog\Exceptions\CommandException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UnitTests\Repositories\UsersRepository\DummyUsersRepository;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
//use GeekBrains\LevelTwo\Blog\UnitTests\DummyLogger;

class CreateUserCommandTest extends TestCase
{

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
    /**
     * @throws ExceptionInterface
     */
    public function testItRequiresLastName(): void
    {
    // Передаём в конструктор команды объект, возвращаемый нашей функцией
    $command = new CreateUser(
        $this->makeUsersRepository(),
    );
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Not enough arguments (missing: "last_name").'
    );

        // Запускаем команду методом run вместо handle
        $command->run(
            // Передаём аргументы как ArrayInput,
        // а не Arguments
        // Сами аргументы не меняются
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'some_password',
                'first_name' => 'Ivan',
        ]),
        // Передаём также объект,
        // реализующий контракт OutputInterface
        // Нам подойдёт реализация,
        // которая ничего не делает
        new NullOutput()
        );
    }
    /**
     * @throws ExceptionInterface
     */
    public function testItRequiresFirstName(): void
    {
        $command = new CreateUser(
            $this->makeUsersRepository(),
        );
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "first_name, last_name")'
        );
        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'pass',
            ]),
            new NullOutput()
        );
    }

    /**
     * @throws ExceptionInterface
     */
    public function testItRequiresPassword(): void
    {
        $command = new CreateUser(
            $this->makeUsersRepository()
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "first_name, last_name, password")'
        );
        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
            ]),
            new NullOutput()
        );
    }

    // Тест, проверяющий, что команда сохраняет пользователя в репозитории

    /**
     * @throws ExceptionInterface
     */
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
        $command = new CreateUser(
            $usersRepository,
        );
        // Запускаем команду
        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'pass',
                'first_name' => 'Ivan',
                'last_name' => 'Nikitin',
            ]),
            new NullOutput()
        );
        // Проверяем утверждение относительно мока,
        // а не утверждение относительно команды
        $this->assertTrue($usersRepository->wasCalled());
    }
}
