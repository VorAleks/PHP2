<?php

namespace GeekBrains\LevelTwo\Blog;

class Post
{
    
    public function __construct(
        private UUID $uuid,
        private User $author,
        private string $title,
        private string $text
    ) {
    }

    public function __toString()
    {
    return $this->author . PHP_EOL 
    . 'Заголовок: ' . $this->title . PHP_EOL 
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
    public function setAuthor(User $author): Post
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get the value of header
     */ 
    public function getTitle(): String
    {
        return $this->title;
    }

    /**
     * Set the value of header
     * @param string $title
     * @return  self
     */
    public function setTitle(string $title): Post
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of text
     */ 
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Set the value of text
     * @param string $text
     * @return  self
     */
    public function setText(string $text): Post
    {
        $this->text = $text;

        return $this;
    }

        
}