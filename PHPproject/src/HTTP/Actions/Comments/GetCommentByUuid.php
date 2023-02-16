<?php


namespace GeekBrains\LevelTwo\Http\Actions\Comments;


use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class GetCommentByUuid implements ActionInterface
{
    public function __construct(
        private CommentsRepositoryInterface $commentsRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            // Пытаемся получить искомый uuid поста из запроса
            $uuid = $request->query('uuid');
        } catch (HttpException $e) {
            // Если в запросе нет параметра username -
            // возвращаем неуспешный ответ,
            // сообщение об ошибке берём из описания исключения
            return new ErrorResponse($e->getMessage());
        }

        try {
            // Пытаемся найти пользователя в репозитории
            $comment = $this->commentsRepository->get(new UUID($uuid));
        } catch (CommentNotFoundException $e) {
            // Если пользователь не найден -
            // возвращаем неуспешный ответ
            return new ErrorResponse($e->getMessage());
        }

        // Возвращаем успешный ответ
        return new SuccessfulResponse([
            'commentAuthor' => $comment->getAuthor()->name()->first() . ' '
                . $comment->getAuthor()->name()->last(),
            'postAuthor' => $comment->getPost()->getAuthor()->name()->first() . ' '
                . $comment->getPost()->getAuthor()->name()->last(),
            'text' => $comment->getText()
        ]);
    }
}