<?php

namespace GeekBrains\LevelTwo\Http\Actions\Likes;

use GeekBrains\LevelTwo\Blog\Exceptions\AppException;
use GeekBrains\LevelTwo\Blog\Exceptions\AuthorDidLikeAlreadyException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\LikePost;
use GeekBrains\LevelTwo\Blog\Repositories\LikesPostsRepository\LikesPostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class CreateLikePost implements ActionInterface
{
    public function __construct(
        private LikesPostsRepositoryInterface $likesPostsRepository,
        private PostsRepositoryInterface $postsRepository,
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
            $postUuid = new UUID($request->jsonBodyField('post_uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }
        try {
            $this->postsRepository->get($postUuid);
        } catch (PostNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newLikePostUuid = UUID::random();

        try {
            $likePost = new LikePost(
                $newLikePostUuid,
                $this->postsRepository->get($postUuid),
                $author
            );
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }
        try {
            $this->likesPostsRepository->save($likePost);
        } catch (AuthorDidLikeAlreadyException $e) {
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfulResponse([
            'uuid' => (string)$newLikePostUuid,
        ]);
    }
}