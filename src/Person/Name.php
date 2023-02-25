<?php

namespace GeekBrains\LevelTwo\Person;

class Name
{
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
     * Get the value of firstName
     */ 
    public function first(): String
    {
        return $this->firstName;
    }

    /**
     * Set the value of firstName
     *
     * @param String $firstName
     * @return  self
     */
    public function setFirstName(String $firstName): Name
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get the value of lastName
     */ 
    public function last(): String
    {
        return $this->lastName;
    }

    /**
     * Set the value of lastName
     *
     * @param String $lastName
     * @return  self
     */
    public function setLastName(String $lastName): Name
    {
        $this->lastName = $lastName;

        return $this;
    }
}
