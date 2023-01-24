<?php

namespace App;

class Comment
{
    private int $id = 1;
    private int $authorId;
    private int $storyId;

    public function __construct(
        private User $author,
        private Story $storyComment,
        private string $text
    )
    {
        $this->authorId = $author->getId();
        $this->storyId = $storyComment->getId();
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of authorId
     */ 
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * Get the value of storyId
     */ 
    public function getStoryId()
    {
        return $this->storyId;
    }
}