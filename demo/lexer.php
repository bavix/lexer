<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$source = <<<template

{% for user in users %}
{% endfor %}

{% for user in users %}
{% endfor %}

template;

$lexer = new \Bavix\Lexer\Lexer();

foreach ($lexer->tokens($source) as $tokens)
{
    foreach ($tokens as $token)
    {
        var_dump($token);
    }
}
