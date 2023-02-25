<?php

//use GeekBrains\LevelTwo\Blog\Commands\Arguments;
//use GeekBrains\LevelTwo\Blog\Commands\CreateUserCommand;
//use GeekBrains\LevelTwo\Blog\Exceptions\AppException;
use GeekBrains\LevelTwo\Blog\Commands\FakeData\PopulateDB;
use GeekBrains\LevelTwo\Blog\Commands\Posts\DeletePost;
use GeekBrains\LevelTwo\Blog\Commands\Users\CreateUser;
use GeekBrains\LevelTwo\Blog\Commands\Users\UpdateUser;
use Symfony\Component\Console\Application;
use Psr\Log\LoggerInterface;

// Подключаем файл bootstrap.php
// и получаем настроенный контейнер
$container = require __DIR__ . '/bootstrap.php';
//$logger = $container->get(LoggerInterface::class);

// Создаём объект приложения
$application = new Application();

// Перечисляем классы команд
$commandsClasses = [
    CreateUser::class,
    DeletePost::class,
    UpdateUser::class,
    // Добавили команду генерирования тестовых данных
    PopulateDB::class,
];

foreach ($commandsClasses as $commandClass) {
    $command = $container->get($commandClass);
    $application->add($command);
}

$application->run();













// создание пользователя без symphony
//try {

    // При помощи контейнера создаём команду
    //    $command = $container->get(CreateUserCommand::class);
    //    $command->handle(Arguments::fromArgv($argv));
    //} catch (AppException $e) {
    ////    $logger->error($e->getMessage(), ['exception' => $e]);
    //    echo "{$e->getMessage()}\n";
    //}

//    $likesPostsRepository = $container->get(LikesPostsRepositoryInterface::class);
//
//    $likesPosts = $likesPostsRepository->getByPostUuid(new UUID('aa53f72e-3ade-43ad-adb0-ac57c12865b2'));
//    print_r($likesPosts);
//
//    die();

//require_once __DIR__ . '/vendor/autoload.php';
//
//use App\Blog\Commands\CommandException;
//use GeekBrains\LevelTwo\Blog\UUID;
//use App\Person\Name;
//use App\Blog\User;
//use App\Blog\Post;
//use App\Blog\Comment;
//use App\Blog\Repositories\UsersRepository\InMemoryUsersRepository;
//use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
//use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
//use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
//use App\Blog\Repositories\UsersRepository\UserNotFoundException;

// Создаём объект SQLite-репозитория

//$connection =  new PDO('sqlite:' . __DIR__ . '/blog.sqlite');
//$usersRepository = new SqliteUsersRepository($connection);
//$postsRepository = new SqlitePostsRepository($connection);
//$commentsRepository = new SqliteCommentsRepository($connection);

// $command = new CreateUserCommand($usersRepository);

// try {
//     // Запускаем команду
//     $command->handle($argv);
//     } catch (CommandException $e) {
//     // Выводим сообщения об ошибках
//     echo "{$e->getMessage()}\n";
//     }

// try {
//     // "Заворачиваем" $argv в объект типа Arguments
//     $command->handle(Arguments::fromArgv($argv));
//     }
//     // Так как мы добавили исключение ArgumentsException
//     // имеет смысл обрабатывать все исключения приложения,
//     // а не только исключение CommandException
//     catch (AppException $e) {
//     echo "{$e->getMessage()}\n";
//     }
        
//    $faker = Faker\Factory::create('ru_RU');

// $name = new Name(
//     $faker->firstName('male'),
//     $faker->lastName('male')
// );

// $user = new User(
//     UUID::random(),
//     $faker->firstName('male'),
//     $name
// );

// $usersRepository->save($user);
// echo $usersRepository->get($id_user);

// switch ($argv[1] ?? null) {
//     case 'user':
//         echo $user;
//         break;
//     case 'post':
//         echo new Post(
//             $faker->randomDigitNotNull(),
//             $user,
//             $faker->sentence(1),
//             $faker->sentence(10)
//         );
//         break;
//     case 'comment':
//         echo new Comment(
//             $faker->randomDigitNotNull(),
//             new User(
//                 $faker->randomDigitNotNull(),
//                 new Name(
//                     $faker->firstName('female'),
//                     $faker->lastName('female')
//                 )
//             ),
//             new Post(
//                 $faker->randomDigitNotNull(),
//                 $user,
//                 $faker->sentence(1),
//                 $faker->sentence(10)
//             ),
//             $faker->sentence(3)    
//         );
//         break;
//     default:
//         echo 'No parametr. (user, post, comment)';
// };
//$id_comment = new UUID('041d76de-15ef-4c56-b0da-c1a2ca644497');
//$id_post = new UUID('fff81673-d726-49e8-b16e-82113319c06c');
//$id_user = new UUID('14504c0d-c9a8-4f9b-996d-3d567f73bc8d');

// $post = new Post(
//     UUID::random(),
//     $usersRepository->get($id),
//     $faker->sentence(3),
//     $faker->sentence(10)
// );
// $postsRepository->save($post);
// echo $postsRepository->get($id);

// echo $comment = new Comment(
//          UUID::random(),
//          $postRepository->get($id_post),
//          $usersRepository->get($id_user),
//         $faker->sentence(10)
//     );
    
    // var_dump($comment->getPost());
// $commentsRepository->save($comment);
//echo $commentsRepository->get($id_comment);
