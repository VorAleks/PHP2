<?php

namespace App\Blog;

class Post
{
    
    public function __construct(
        private int $id,
        private User $author,
        private string $header,
        private string $text
    ) {
    }

    public function __toString()
    {
    return $this->author . PHP_EOL 
    . 'Заголовок: ' . $this->header . PHP_EOL 
    . $this->text;
    }

    /**
     * Get the value of id
     */ 
    public function id(): Int
    {
        return $this->id;
    }

        /**
     * Get the value of header
     */ 
    public function getHeader(): String
    {
        return $this->header;
    }

    /**
     * Set the value of header
     *
     * @return  self
     */ 
    public function setHeader(string $header): Post
    {
        $this->header = $header;

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
     *
     * @return  self
     */ 
    public function setText(string $text): Post
    {
        $this->text = $text;

        return $this;
    }
}