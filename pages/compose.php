<?php

echo 'COMPOSE';

$article = naju_news_article::fromData(['title' => 'FOO', 'subtitle' => 'bar', 'baz' => 'Haxx0r']);

dump($article);
