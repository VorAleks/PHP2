<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesCommentsRepository;

use GeekBrains\LevelTwo\Blog\LikeComment;
use GeekBrains\LevelTwo\Blog\UUID;

interface LikesCommentsRepositoryInterface
{
    public function save(likeComment $like): void;
    public function getByCommentUuid(UUID $uuid): array;
}