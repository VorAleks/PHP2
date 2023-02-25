<?php

namespace GeekBrains\LevelTwo\Blog;

class Comment
{
   
    public function __construct(
        private UUID $uuid,
        private Post $post,
        private User $author,
        private string $text
    ) {
    }

    public function __toString()
    {
    return $this->post . PHP_EOL 
        . ' was commented by '
        . $this->author . PHP_EOL
        . $this->text;
    }
    /**
     * Get the value of id
     */ 
    public function uuid(): UUID
    {
        return $this->uuid;
    }
    
     /**
     * Get the value of storyComment
     */ 
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * Set the value of storyComment
     * @param Post $post
     * @return  self
     */
    public function setPost(Post $post): Comment
    {
        $this->post = $post;

        return $this;
    }
    
    /**
     * Get the value of author
     */ 
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * Set the value of author
     * @param User $author
     * @return  self
     */
    public function setAuthor(User $author): Comment
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get the value of text
     */ 
    public function getText(): String
    {
        return $this->text;
    }

    /**
     * Set the value of text
     * @param $text
     * @return  self
     */
    public function setText($text): Comment
    {
        $this->text = $text;

        return $this;
    }
}