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
        $fragments = $this->lexer->tokens($source);

        // empty
        $this->assertEquals([], $fragments[Lexer::RAW]);
        $this->assertEquals([], $fragments[Lexer::OPERATOR]);
        $this->assertEquals([], $fragments[Lexer::LITERAL]);

        $this->assertCount(1, $fragments[Lexer::PRINTER]);

        /**
         * Get first fragment
         * @var array $fragment
         */
        $fragment = reset($fragments[Lexer::PRINTER]);

        $this->assertEquals(4, $fragment['type']);
        $this->assertTrue($fragment['print']);
        $this->assertTrue($fragment['escape']);
        $this->assertEquals('T_VAR', $fragment['name']);
        $this->assertEquals(trim($source), $fragment['code']);
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
