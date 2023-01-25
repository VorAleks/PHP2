<?php

namespace App\Blog\Repositories\UsersRepository;

use App\Blog\User;

class InMemoryUsersRepository
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
public function get(int $id): User
{
foreach ($this->users as $user) {
if ($user->id() === $id) {
return $user;
}
}
throw new UserNotFoundException("User not found: $id");
}
}
