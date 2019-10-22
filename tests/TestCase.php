<?php

namespace Bavix\Test;

use Bavix\Lexer\Lexer;

class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Lexer
     */
    protected $lexer;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->lexer = new Lexer();
        parent::setUp();
    }

}
