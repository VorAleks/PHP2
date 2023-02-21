<?php

namespace GeekBrains\LevelTwo\Http\Actions\Likes;

use GeekBrains\LevelTwo\Blog\Exceptions\AppException;
use GeekBrains\LevelTwo\Blog\Exceptions\AuthorDidLikeAlreadyException;
use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\LikeComment;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\LikesCommentsRepository\LikesCommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class CreateLikeComment implements \GeekBrains\LevelTwo\Http\Actions\ActionInterface
{
    public function __construct(
        private LikesCommentsRepositoryInterface $likesCommentsRepository,
        private CommentsRepositoryInterface $commentsRepository,
//        private UsersRepositoryInterface $usersRepository
        // Аутентификация по токену
        private TokenAuthenticationInterface $authentication,
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            $author = $this->authentication->user($request);
        } catch (AppException $e) {
            return new ErrorResponse($e->getMessage());
        }
//        try {
//            $authorUuid = new UUID($request->jsonBodyField('author_uuid'));
//        } catch (HttpException | InvalidArgumentException $e) {
//            return new ErrorResponse($e->getMessage());
//        }
//        try {
//            $this->usersRepository->get($authorUuid);
//        } catch (UserNotFoundException $e) {
//            return new ErrorResponse($e->getMessage());
//        }

        try {
            $commentUuid = new UUID($request->jsonBodyField('comment_uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }
        try {
            $this->commentsRepository->get($commentUuid);
        } catch (CommentNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newLikeCommentUuid = UUID::random();

        try {
            $likeComment = new LikeComment(
                $newLikeCommentUuid,
                $this->commentsRepository->get($commentUuid),
                $author
            );
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }
        try {
            $this->likesCommentsRepository->save($likeComment);
        } catch (AuthorDidLikeAlreadyException $e) {
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfulResponse([
            'uuid' => (string)$newLikeCommentUuid,
        ]);
    }
}