<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesPostsRepository;

use GeekBrains\LevelTwo\Blog\LikePost;
use GeekBrains\LevelTwo\Blog\UUID;

interface LikesPostsRepositoryInterface
{
    public function save(likePost $like): void;
    public function get(UUID $uuid): LikePost;
    public function getLikesByPostUuid(UUID $uuid): array;
}