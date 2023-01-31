<?php

namespace App\Blog;

use App\Person\Name;

class User
{
    public function __construct(
    private UUID $uuid,
    private string $username,
    private Name $name
    ) {
    }
    public function __toString()
    {
    return $this->username() . ' ' . $this->name;
    }    

    /**
     * Get the value of uuid
     */ 
    public function uuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * Get the value of username
     */ 
    public function username(): string
    {
        return $this->username;
    }

    /**
     * Get the value of name
     */ 
    public function name()
    {
        return $this->name;
    }
}
