<?php

namespace App\Blog\Repositories\UsersRepository;

use App\Blog\User;
use App\Blog\UUID;

class InMemoryUsersRepository implements UsersRepositoryInterface
{
    /**
    * @var User[]
    */
    private array $users = [];
    /**
    * @param User $user
    */
    public function save(User $user): void
    {
    $this->users[] = $user;
    }
    /**
    * @param int $id
    * @return User
    * @throws UserNotFoundException
    */
    public function get(UUID $uuid): User
    {
    foreach ($this->users as $user) {
    if ($user->uuid() === $uuid) {
    return $user;
    }
    }
    throw new UserNotFoundException("User not found: $uuid");
    }

    public function getByUsername(string $username): User
    {   
    foreach ($this->users as $user) {
    if ($user->username() === $username) {
    return $user;
    }
    }
    throw new UserNotFoundException("User not found: $username");
    }
}
