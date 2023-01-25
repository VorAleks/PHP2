<?php

namespace App\Blog;

class Comment
{
   
    public function __construct(
        private int $id,
        private User $author,
        private Post $storyComment,
        private string $text
    ) {
    }

    public function __toString()
    {
    return $this->storyComment . PHP_EOL 
        . 'was commented by ' . $this->author . PHP_EOL 
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
     * Get the value of text
     */ 
    public function getText(): String
    {
        return $this->text;
    }

    /**
     * Set the value of text
     *
     * @return  self
     */ 
    public function setText($text): Comment
    {
        $this->text = $text;

        return $this;
    }
}