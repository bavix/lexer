<?php

namespace Bavix\Test;

use Bavix\Lexer\Lexer;
use Bavix\Lexer\Token;

class VariableTest extends TestCase
{

    /**
     * @param string $source
     * @return void
     * @dataProvider dataProviderSource
     */
    public function testSimple(string $source): void
    {
        $lexObject = $this->lexer->lexerObject($source);
        $lexemes = $lexObject->getLexemes();

        // empty
        $this->assertEquals([], $lexemes[Lexer::RAW]);
        $this->assertEquals([], $lexemes[Lexer::OPERATOR]);
        $this->assertEquals([], $lexemes[Lexer::LITERAL]);

        $this->assertCount(1, $lexemes[Lexer::PRINTER]);

        /**
         * Get first fragment
         * @var array $fragment
         */
        $fragment = reset($lexemes[Lexer::PRINTER]);

        $this->assertEquals(Lexer::PRINTER, $fragment['type']);
        $this->assertTrue($fragment['print']);
        $this->assertTrue($fragment['escape']);
        $this->assertEquals('T_VAR', $fragment['name']);
        $this->assertEquals($lexObject->getSource(), $lexObject->getTemplate());
        $this->assertEquals(trim($lexObject->getTemplate()), $fragment['code']);
        $this->assertEquals('var', $fragment['fragment']);
        $this->assertCount(1, $fragment['tokens']);

        /**
         * Get first token
         *
         * @var Token $token
         */
        $token = reset($fragment['tokens']);

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals('var', $token->token);
        $this->assertEquals(T_VAR, $token->type);
        $this->assertEquals('T_VAR', $token->name);
    }

    /**
     * @return array
     */
    public function dataProviderSource(): array
    {
        return [
            ['{{var }}'],
            ['{{var}}'],
            ['{{ var}}'],
            ['{{ var }}'],
            ['  {{ var }}'],
            ['  {{ var }}   '],
            ['  {{ var  }}'],
            ['  {{ var}}    '],
            ['  {{      var }}           '],
        ];
    }

}
