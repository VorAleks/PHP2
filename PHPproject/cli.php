<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Person\Name;
use App\Blog\User;
use App\Blog\Post;
use App\Blog\Comment;
use App\Blog\Repositories\UsersRepository\InMemoryUsersRepository;
use App\Blog\Repositories\UsersRepository\UserNotFoundException;

$faker = Faker\Factory::create('ru_RU');

$name = new Name(
    $faker->firstName('male'),
    $faker->lastName('male')
);

$user = new User(
    $faker->randomDigitNotNull(),
    $name
);

switch ($argv[1] ?? null) {
    case 'user':
        echo $user;
        break;
    case 'post':
        echo new Post(
            $faker->randomDigitNotNull(),
            $user,
            $faker->sentence(1),
            $faker->sentence(10)
        );
        break;
    case 'comment':
        echo new Comment(
            $faker->randomDigitNotNull(),
            new User(
                $faker->randomDigitNotNull(),
                new Name(
                    $faker->firstName('female'),
                    $faker->lastName('female')
                )
            ),
            new Post(
                $faker->randomDigitNotNull(),
                $user,
                $faker->sentence(1),
                $faker->sentence(10)
            ),
            $faker->sentence(3)    
        );
        break;
    default:
        echo 'No parametr. (user, post, comment)';
};

// $post = new Post(
//     1,
//     new User(
//         1,
//         new Name('Иван', 'Никитин'),
//     ),
//     'Day 1',
//     'Starting PHP Project'
// );
// print $post;

// echo PHP_EOL . PHP_EOL;

// $comment = new Comment(
//     34343,
//     new User(2, new Name('John', 'Dow')),
//     $post,
//     'Good luck!'
// );
// print $comment . PHP_EOL;


// $repository = new InMemoryUsersRepository();
// try {
// $repository->save(new User(123, new Name('AAA','bbbbbbbb')));
// $repository->save(new User(324, new Name('ccc','DDDDDDDD')));
// echo $repository->get(324) . PHP_EOL;
// echo $repository->get(123) . PHP_EOL;
// echo $repository->get(555) . PHP_EOL;
// } catch (UserNotFoundException | Exception $e) {
//     echo $e->getMessage();
// }