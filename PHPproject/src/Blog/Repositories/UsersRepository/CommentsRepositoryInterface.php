<?php

namespace App\Blog\Repositories\UsersRepository;

use App\Blog\Comment;
use App\Blog\UUID;

interface CommentsRepositoryInterface
{
    public function save(Comment $comment): void;
    public function get(UUID $uuid): Comment;
}