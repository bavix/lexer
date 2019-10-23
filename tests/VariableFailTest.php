<?php

namespace Bavix\Test;

use Bavix\Exceptions\Blank;
use Bavix\Exceptions\Logic;
use Bavix\Exceptions\Runtime;

class VariableFailTest extends TestCase
{

    /**
     * @param string $source
     * @param string $class
     * @return void
     * @dataProvider dataProviderSource
     */
    public function testSimple(string $source, string $class): void
    {
        $this->expectException($class);
        $this->lexer->lexerObject($source);
    }

    /**
     * @return array
     */
    public function dataProviderSource(): array
    {
        return [
            ['{!var %}', Runtime::class],
            ['{! {!var !} %}', Logic::class],
            ['{! var !} %}', Logic::class],
            ['{% %}', Blank::class],
            ['{! !}', Blank::class],
            ['{{ }}', Blank::class],
            ['{{ !}', Runtime::class],
            ['{{ hello. world }}', Runtime::class],
            ['{{ for a in test !}', Runtime::class],
            ['{{ \'test }}', \ParseError::class],
            ['{{ "\'test }}', \ParseError::class],
            ['{{ "test }}', \ParseError::class],
            ['{{ te{st}}} }}', Logic::class],
            ['{% literal %}', Logic::class],
            ['{% endliteral %}', Logic::class],
        ];
    }

}
