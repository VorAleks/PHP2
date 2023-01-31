<?php

namespace App\Blog;

use App\Person\Name;

class User
{
    public function __construct(
    private int $id,
    private Name $name
    ) {
    }
    public function __toString()
    {
    return $this->name;
    }    

    /**
     * Get the value of id
     */ 
    public function id(): Int
    {
        return $this->id;
    }
}
