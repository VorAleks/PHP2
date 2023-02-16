<?php

namespace GeekBrains\LevelTwo\Http\Actions\Likes;

use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikePostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\LikesPostsRepository\LikesPostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class GetLikePostByUuid implements ActionInterface
{
    public function __construct(
        private LikesPostsRepositoryInterface $likesPostsRepository,
    ) {
    }
    public function handle(Request $request): Response
    {
        try {
            // Пытаемся получить искомый uuid поста из запроса
            $uuid = $request->query('uuid');
            $likePostUuid = new UUID($uuid);
        } catch (HttpException | InvalidArgumentException $e) {
            // возвращаем неуспешный ответ,
            // сообщение об ошибке берём из описания исключения
            return new ErrorResponse($e->getMessage());
        }

        try {
            // Пытаемся найти лайк  в репозитории
            $likePost = $this->likesPostsRepository->get($likePostUuid);
        } catch (LikePostNotFoundException $e) {
            // Если пользователь не найден -
            // возвращаем неуспешный ответ
            return new ErrorResponse($e->getMessage());
        }

        // Возвращаем успешный ответ
        return new SuccessfulResponse([
            'postAuthor' => $likePost->getPost()->getAuthor()->name()->first() . ' '
                . $likePost->getPost()->getAuthor()->name()->last(),
            'likeAuthor' => $likePost->getAuthor()->name()->first() . ' '
                . $likePost->getAuthor()->name()->last(),
        ]);
    }
}