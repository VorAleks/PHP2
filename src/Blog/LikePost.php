<?php


namespace GeekBrains\LevelTwo\Blog;


use JetBrains\PhpStorm\Pure;

class LikePost
{
    public function __construct(
        private UUID $uuid,
        private Post $post,
        private User $author
    )
    {
    }

    #[Pure] public function __toString()
    {
        return $this->getAuthor()->name()
            . ' поставил лайк посту '
            . $this->post->getAuthor()->name();
    }

    /**
     * @return UUID
     */
    public function uuid(): UUID
    {
        return $this->uuid;
    }
    /**
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * @param Post $post
     * @return LikePost
     */
    public function setPost(Post $post): LikePost
    {
        $this->post = $post;

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
     * @return LikePost
     */
    public function setAuthor(User $author): LikePost
    {
        $this->author = $author;

        return $this;
    }
}
