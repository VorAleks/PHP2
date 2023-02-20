<?php
//Создаём объект подключения к SQLite
$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

$connection->exec('CREATE TABLE users (
    uuid TEXT NOT NULL
    CONSTRAINT uuid_primary_key PRIMARY KEY,
    username TEXT NOT NULL
    CONSTRAINT username_unique_key UNIQUE,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL
    )');

$connection->exec('CREATE TABLE posts (
    uuid TEXT NOT NULL
    CONSTRAINT uuid_primary_key PRIMARY KEY,
    author_uuid TEXT NOT NULL,
    title TEXT NOT NULL,
    text TEXT NOT NULL
    )');

$connection->exec('CREATE TABLE comments (
    uuid TEXT NOT NULL
    CONSTRAINT uuid_primary_key PRIMARY KEY,
    post_uuid TEXT NOT NULL,
    author_uuid TEXT NOT NULL,
    text TEXT NOT NULL
    )');

$connection->exec('CREATE TABLE likes_posts (
    uuid TEXT NOT NULL
    CONSTRAINT uuid_primary_key PRIMARY KEY,
    post_uuid TEXT NOT NULL,
    author_uuid TEXT NOT NULL
    )');

// //Вставляем строку в таблицу пользователей
// $connection->exec(
// "INSERT INTO users (first_name, last_name) VALUES ('Ivan', 'Nikitin')"
// );