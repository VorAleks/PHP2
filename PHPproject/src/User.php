<?php

namespace App;

class User
{
    private int $id = 1;

    public function __construct(
    private string $firstName,
    private string $lastName
    ) {
    }
    public function __toString()
    {
    return $this->firstName . ' ' . $this->lastName;
    }
    

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }
}
