<?php


namespace GeekBrains\LevelTwo\Http\Actions\Likes;


use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikeCommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\LikesCommentsRepository\LikesCommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class GetLikeCommentByUuid implements ActionInterface
{
    public function __construct(
        private LikesCommentsRepositoryInterface $likesCommentsRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            // Пытаемся получить искомый uuid поста из запроса
            $uuid = $request->query('uuid');
            $likeCommentUuid = new UUID($uuid);
        } catch (HttpException | InvalidArgumentException$e) {
            // возвращаем неуспешный ответ,
            // сообщение об ошибке берём из описания исключения
            return new ErrorResponse($e->getMessage());
        }

        try {
            // Пытаемся найти лайк в репозитории
            $likeComment = $this->likesCommentsRepository->get($likeCommentUuid);
        } catch (LikeCommentNotFoundException $e) {
            // Если пользователь не найден -
            // возвращаем неуспешный ответ
            return new ErrorResponse($e->getMessage());
        }

        // Возвращаем успешный ответ
        return new SuccessfulResponse([
            'commentAuthor' => $likeComment->getComment()->getAuthor()->name()->first() . ' ' . $likeComment->getComment()->getAuthor()->name()->last(),
            'likeAuthor' => $likeComment->getAuthor()->name()->first() . ' ' . $likeComment->getAuthor()->name()->last(),
        ]);
    }
}