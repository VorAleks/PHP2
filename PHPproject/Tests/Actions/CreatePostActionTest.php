<?php

namespace Actions;

use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\Posts\CreatePost;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use PhpParser\JsonDecoder;
use PHPUnit\Framework\TestCase;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;


class CreatePostActionTest extends TestCase
{
    private function postsRepository(): PostsRepositoryInterface
    {
        return new class() implements PostsRepositoryInterface{
            private bool $called = false;

            public function __construct()
            {
            }

            public function save(Post $post): void
            {
                $this->called = true;
            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException('Not found');
            }

            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException('Not found');
            }

            public function getCalled(): bool
            {
                return $this->called;
            }

            public function delete(UUID $uuid):  void
            {
            }

        };
    }

    private function usersRepository(array $users): UsersRepositoryInterface
    {
        // В конструктор анонимного класса передаём массив пользователей
        return new class($users) implements UsersRepositoryInterface {
            public function __construct(
                private array $users
            ) {
            }
            public function save(User $user): void
            {
            }
            public function get(UUID $uuid): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && (string)$uuid == $user->uuid())
                    {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Can not find user: " . $uuid);
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
        };
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disable
     */
    public function testItReturnSuccessfulResponse(): void
    {
        $request = new Request([], [], '{"author_uuid":"14504c0d-c9a8-4f9b-996d-3d567f73bc8d","title":"some title","text":"some comment"}');

        $postsRepository = $this->postsRepository();

        $usersRepository = $this->usersRepository([
            new User(
              new UUID('14504c0d-c9a8-4f9b-996d-3d567f73bc8d'),
                'username',
                new Name('name', 'surname'),
            ),
        ]);

        $action = new CreatePost($postsRepository, $usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);

        $this->setOutputCallback(function ($data) {
            $dataDecode = json_decode(
                $data,
                associative: true,flags: JSON_THROW_ON_ERROR
            );

            $dataDecode['data']['uuid'] = "aa53f72e-3ade-43ad-adb0-ac57c12865b2";

            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $this->expectOutputString('{"success":true,"data":{"uuid":"aa53f72e-3ade-43ad-adb0-ac57c12865b2"}}');
        $response->send();
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disable
     */
    public function testItReturnsErrorResponseIfUuidHasWrongFormat(): void
    {
        // Создаём объект запроса
        // Вместо суперглобальных переменных
        // передаём простые массивы
        $request = new Request([], [], '{
            "author_uuid": "da912a70-5b94-4a63-93d3-error783944510017",
            "title": "some title",
            "text": "some comment"
        }');

        // Создаём стаб репозитория пользователей
        $postsRepository = $this->postsRepository();
        $usersRepository = $this->usersRepository([]);

        //Создаём объект действия
        $action = new CreatePost($postsRepository, $usersRepository);

        // Запускаем действие
        $response = $action->handle($request);

        // Проверяем, что ответ - неудачный
        $this->assertInstanceOf(ErrorResponse::class, $response);

        // Описываем ожидание того, что будет отправлено в поток вывода
        $this->expectOutputString('{"success":false,"reason":"Malformed UUID: da912a70-5b94-4a63-93d3-error783944510017"}');

        // Отправляем ответ в поток вывода
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disable
     */
    public function testItReturnsErrorResponseIfNotFoundUser(): void
    {
        $request = new Request([], [], '{
            "author_uuid": "da912a70-5b94-4a63-93d3-783944510017",
            "title": "some title",
            "text": "some comment"
        }');

        // Создаём стаб репозитория пользователей
        $postsRepository = $this->postsRepository();
        $usersRepository = $this->usersRepository([]);

        //Создаём объект действия
        $action = new CreatePost($postsRepository, $usersRepository);

        // Запускаем действие
        $response = $action->handle($request);

        // Проверяем, что ответ - неудачный
        $this->assertInstanceOf(ErrorResponse::class, $response);

        // Описываем ожидание того, что будет отправлено в поток вывода
        $this->expectOutputString('{"success":false,"reason":"Can not find user: da912a70-5b94-4a63-93d3-783944510017"}');

        // Отправляем ответ в поток вывода
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disable
     */
    public function testItReturnsErrorResponseIfNoTitle(): void
    {
        $request = new Request([], [], '{
            "author_uuid": "da912a70-5b94-4a63-93d3-783944510017",

            "text": "some comment"
        }');

        // Создаём стаб репозитория пользователей
        $postsRepository = $this->postsRepository();
        $usersRepository = $this->usersRepository([
            new User(
              new UUID('da912a70-5b94-4a63-93d3-783944510017'),
                'username',
                new Name('name', 'surname'),
            ),
        ]);

        //Создаём объект действия
        $action = new CreatePost($postsRepository, $usersRepository);

        // Запускаем действие
        $response = $action->handle($request);

        // Проверяем, что ответ - неудачный
        $this->assertInstanceOf(ErrorResponse::class, $response);

        // Описываем ожидание того, что будет отправлено в поток вывода
        $this->expectOutputString('{"success":false,"reason":"No such field: title"}');

        // Отправляем ответ в поток вывода
        $response->send();
    }

}