<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\User;
use App\Story;
use App\Comment;

$post = new Story(
new User('Иван', 'Никитин'),
'Day 1',
'Starting PHP Project'
);
print $post;

echo PHP_EOL . PHP_EOL;

$comment = new Comment(
    new User('John', 'Dow'),
    $post,
    'Good luck!'
);
print $comment;