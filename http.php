<?php

use GeekBrains\LevelTwo\Blog\Exceptions\AppException;
use GeekBrains\LevelTwo\Http\Actions\Auth\LogIn;
use GeekBrains\LevelTwo\Http\Actions\Comments\CreateComment;
use GeekBrains\LevelTwo\Http\Actions\Comments\GetCommentByUuid;
use GeekBrains\LevelTwo\Http\Actions\Likes\CreateLikeComment;
use GeekBrains\LevelTwo\Http\Actions\Likes\CreateLikePost;
use GeekBrains\LevelTwo\Http\Actions\Likes\GetLikeCommentByUuid;
use GeekBrains\LevelTwo\Http\Actions\Likes\GetLikePostByUuid;
use GeekBrains\LevelTwo\Http\Actions\Likes\GetLikesByCommentUuid;
use GeekBrains\LevelTwo\Http\Actions\Likes\GetLikesByPostUuid;
use GeekBrains\LevelTwo\Http\Actions\Posts\GetPostByUuid;
use GeekBrains\LevelTwo\Http\Actions\Users\CreateUser;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Actions\Users\FindByUsername;
use GeekBrains\LevelTwo\Http\Actions\Posts\CreatePost;
use GeekBrains\LevelTwo\Http\Actions\Posts\DeletePost;
use Psr\Log\LoggerInterface;

// Подключаем файл bootstrap.php
// и получаем настроенный контейнер
$container = require __DIR__ . '/bootstrap.php';

$logger = $container->get(LoggerInterface::class);

$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input'),
);
try {
    $path = $request->path();
} catch (HttpException $e) {
    $logger->warning($e->getMessage());
    (new ErrorResponse)->send();
    return;
}
try {
    $method = $request->method();
} catch (HttpException $e) {
    $logger->warning($e->getMessage());
    (new ErrorResponse)->send();
    return;
}
// Ассоциируем маршруты с именами классов действий,
// вместо готовых объектов
$routes = [
    'GET' => [
        '/users/show' => FindByUsername::class,
        '/posts/show' => GetPostByUuid::class,
        '/comments/show' => GetCommentByUuid::class,
        '/likes-posts/show/like' => GetLikePostByUuid::class,
        '/likes-posts/show/list' => GetLikesByPostUuid::class,
        '/likes-comments/show/like' => GetLikeCommentByUuid::class,
        '/likes-comments/show/list' => GetLikesByCommentUuid::class,
    ],
    'POST' => [
        // Добавили маршрут обмена пароля на токен
        '/login' => LogIn::class,
        '/users/create' => CreateUser::class,
        '/posts/create' => CreatePost::class,
        '/comments/create' => CreateComment::class,
        '/likes-posts/create' => CreateLikePost::class,
        '/likes-comments/create' => CreateLikeComment::class,
    ],

    'DELETE' => [
        '/posts' => DeletePost::class,
    ],
];

if (!array_key_exists($method, $routes)
    || !array_key_exists($path, $routes[$method])) {
    $message = "Route not found: $method $path";
    $logger->notice($message);
    (new ErrorResponse("Route not found: $method $path"))->send();
    return;
}

// Получаем имя класса действия для маршрута
$actionClassName = $routes[$method][$path];

try {
    // С помощью контейнера
    // создаём объект нужного действия
    $action = $container->get($actionClassName);
    $response = $action->handle($request);
} catch (AppException $e) {
    //Logging message with ERROR level
    $logger->error($e->getMessage(), ['exception' => $e]);
    //Dont sent error message to user, only logging
    (new ErrorResponse)->send();
}

$response->send();















//use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
//use GeekBrains\LevelTwo\Http\Actions\Comments\CreateComment;

//use GeekBrains\LevelTwo\Http\Actions\Users\CreateUser;




//use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
//use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;

//
//require_once __DIR__ . '/vendor/autoload.php';
//
//// Создаём объект запроса из суперглобальных переменных
//$request = new Request($_GET, $_SERVER, file_get_contents('php://input'),);
//
//try {
//// Пытаемся получить путь из запроса
//    $path = $request->path();
//} catch (HttpException) {
//// Отправляем неудачный ответ,
//// если по какой-то причине
//// не можем получить путь
//    (new ErrorResponse)->send();
//// Выходим из программы
//    return;
//}
//
//try {
//// Пытаемся получить HTTP-метод запроса
//    $method = $request->method();
//} catch (HttpException) {
//// Возвращаем неудачный ответ,
//// если по какой-то причине
//// не можем получить метод
//    (new ErrorResponse)->send();
//    return;
//}
//
//
//$routes = [
//    // Добавили ещё один уровень вложенности
//    // для отделения маршрутов,
//    // применяемых к запросам с разными методами
//        'GET' => [
//            '/users/show' => new FindByUsername(
//                new SqliteUsersRepository(
//                    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
//    )
//    ),
//    //'/posts/show' => new FindByUuid(
//    //    new SqlitePostsRepository(
//    //        new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
//    //    )
//    //),
//    ],
//    'POST' => [
//        // Добавили новый маршрут
//        '/users/create' => new CreateUser(
//            new SqliteUsersRepository(
//                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
//            )
//        ),
//        '/posts/create' => new CreatePost(
//            new SqlitePostsRepository(
//                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
//            ),
//            new SqliteUsersRepository(
//                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
//            )
//        ),
//        '/posts/comment' => new CreateComment(
//            new SqliteCommentsRepository(
//                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
//            ),
//            new SqlitePostsRepository(
//                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
//            ),
//            new SqliteUsersRepository(
//                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
//            ),
//        )
//    ],
//    'DELETE' => [
//        '/posts' => new DeletePost(
//            new SqlitePostsRepository(
//                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
//            )
//        )
//          ],
//];
//
//// Если у нас нет маршрутов для метода запроса -
//// возвращаем неуспешный ответ
//if (!array_key_exists($method, $routes)) {
//    (new ErrorResponse('Not found'))->send();
//    return;
//}
//
//// Ищем маршрут среди маршрутов для этого метода
//if (!array_key_exists($path, $routes[$method])) {
//    (new ErrorResponse('Not found'))->send();
//    return;
//}
//
//// Выбираем найденное действие
//$action = $routes[$method][$path];
//
//try {
//// Пытаемся выполнить действие,
//// при этом результатом может быть
//// как успешный, так и неуспешный ответ
//    $response = $action->handle($request);
//    $response->send();
//} catch (Exception $e) {
//// Отправляем неудачный ответ,
//// если что-то пошло не так
//    (new ErrorResponse($e->getMessage()))->send();
//}



