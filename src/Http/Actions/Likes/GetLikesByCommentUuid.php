<?php


namespace GeekBrains\LevelTwo\Http\Actions\Likes;



use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikesForCommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\LikesCommentsRepository\LikesCommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use PhpParser\JsonDecoder;

class GetLikesByCommentUuid implements \GeekBrains\LevelTwo\Http\Actions\ActionInterface
{
    public function __construct(
        private LikesCommentsRepositoryInterface $likesCommentsRepository,
        private CommentsRepositoryInterface $commentsRepository,
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            $commentUuid = new UUID($request->query('uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $this->commentsRepository->get($commentUuid);
        } catch (CommentNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $likesList = $this->likesCommentsRepository->getLikesByCommentUuid($commentUuid);
        } catch (LikesForCommentNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $likesString = '';
        foreach ($likesList as $item){
            $likesString .=  $item->getAuthor()->name() . ' ';
        }

        return new SuccessfulResponse([
            'comment' => (string)$commentUuid,
            'likes' => $likesString,
        ]);
    }
}