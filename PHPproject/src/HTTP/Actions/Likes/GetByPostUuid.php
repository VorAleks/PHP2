<?php

namespace GeekBrains\LevelTwo\Http\Actions\Likes;

use GeekBrains\LevelTwo\Blog\Exceptions\LikesForPostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\LikesPostsRepository\LikesPostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class GetByPostUuid implements ActionInterface
{

    public function __construct(
        private LikesPostsRepositoryInterface $likesPostsRepository,
        private PostsRepositoryInterface $postsRepository,
    ) {
    }

    public function handle(Request $request): Response
    {
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


        try {
            $likesList = $this->likesPostsRepository->getByPostUuid($postUuid);
        } catch (LikesForPostNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }
        $likesString = '';
        foreach ($likesList as $item){
            $likesString .= $item->getAuthor()->username() . ' ';
        }

        return new SuccessfulResponse([
            'post' => (string)$postUuid,
            'likes' => $likesString,
        ]);
    }
}