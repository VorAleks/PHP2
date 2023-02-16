<?php

namespace GeekBrains\LevelTwo\Blog;

use JetBrains\PhpStorm\Pure;

class LikeComment
{
    public function __construct(
        private UUID $uuid,
        private Comment $comment,
        private User $author
    )
    {
    }

    #[Pure] public function __toString()
    {
        return $this->getAuthor()->name() . ' поставил лайк комменту ' . $this->comment->getAuthor()->name();
    }

    /**
     * @return UUID
     */
    public function uuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @return Comment
     */
    public function getComment(): Comment
    {
        return $this->comment;
    }

    /**
     * @param Comment $comment
     * @return LikeComment
     */
    public function setComment(Comment $comment): LikeComment
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @param User $author
     * @return LikeComment
     */
    public function setAuthor(User $author): LikeComment
    {
        $this->author = $author;

        return $this;
    }

}