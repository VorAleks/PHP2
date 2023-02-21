<?php

namespace GeekBrains\LevelTwo\Http\Actions\Posts;

use GeekBrains\LevelTwo\Blog\Exceptions\AppException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class CreatePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
        // Аутентификация по токену
        private TokenAuthenticationInterface $authentication,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle(Request $request): Response
    {
        // Обрабатываем ошибки аутентификации
        // и возвращаем неудачный ответ
        // с сообщением об ошибке
        try {
            $author = $this->authentication->user($request);
        } catch (AppException $e) {
            return new ErrorResponse($e->getMessage());
        }

        // Генерируем UUID для новой статьи
        $newPostUuid = UUID::random();

        try {
        // Пытаемся создать объект статьи
        // из данных запроса
            $post = new Post(
                $newPostUuid,
                $author,
                $request->jsonBodyField('title'),
                $request->jsonBodyField('text'),
            );
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        // Сохраняем новую статью в репозитории
        $this->postsRepository->save($post);

        // Возвращаем успешный ответ,
        // содержащий UUID новой статьи
        return new SuccessfulResponse([
            'uuid' => (string)$newPostUuid,
        ]);
    }
}