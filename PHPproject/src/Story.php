<?php

namespace App;

class Story
{
    private int $id = 1;
    private int $authorId;

    public function __construct(
        private User $author,
        private string $header,
        private string $text
    ) {
        $this->authorId = $author->getId();
    }
    public function __toString()
    {
    return $this->author . PHP_EOL . $this->header . ' ' . $this->text;
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
}