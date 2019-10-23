<?php

namespace Bavix\Test;

use Bavix\Lexer\Lexer;
use Bavix\Lexer\Token;

class ValidatorTest extends TestCase
{

    /**
     * @return void
     */
    public function testLiteral(): void
    {
        $lexerObject = $this->lexer->lexerObject('{% literal %}{{hello!}{% endliteral %}');
        $lexemes = $lexerObject->getLexemes();

        $this->assertCount(1, $lexemes[Lexer::LITERAL]);
        $this->assertCount(0, $lexemes[Lexer::PRINTER]);
        $this->assertCount(0, $lexemes[Lexer::OPERATOR]);
        $this->assertCount(0, $lexemes[Lexer::RAW]);

        $literals = $lexemes[Lexer::LITERAL];
        $key = key($literals);
        $code = current($literals);

        $this->assertStringStartsWith('[!literal::read(', $key);
        $this->assertStringEndsWith(')!]', $key);
        $this->assertRegExp('~\(\d+\)~', $key);

        $this->assertEquals($code, '{{hello!}');
    }

}
