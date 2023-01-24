<?php

namespace GeekBrains\sub_dir;

use GeekBrains\Person\Person;

class Blog_Post
{
public function __construct(
private Person $author,
private string $text
) {
}
public function __toString()
{
return $this->author . ' пишет: ' . $this->text;
}
}
