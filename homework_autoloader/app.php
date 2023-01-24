<?php

use GeekBrains\sub_dir\Blog_Post;
use GeekBrains\Person\Name;
use GeekBrains\Person\Person;

spl_autoload_register(function ($class) {
    $file = str_replace(['\\', ], DIRECTORY_SEPARATOR, $class) . '.php';
    $file =preg_replace_callback(
        '(_[a-z0-9A-Z]+.php)',
         function($match){
            return substr_replace($match[0], DIRECTORY_SEPARATOR, 0, 1);
         },
         $file);
    if (file_exists($file)) {
    require $file;
    }
    });

$post = new Blog_Post(
new Person(
new Name('Иван', 'Никитин'),
new DateTimeImmutable()
),
'Всем привет!'
);
print $post;