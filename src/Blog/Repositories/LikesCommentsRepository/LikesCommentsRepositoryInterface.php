<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesCommentsRepository;

use GeekBrains\LevelTwo\Blog\LikeComment;
use GeekBrains\LevelTwo\Blog\UUID;

interface LikesCommentsRepositoryInterface
{
    public function save(LikeComment $like): void;
    public function get(UUID $uuid): LikeComment;
    public function getLikesByCommentUuid(UUID $uuid): array;
}