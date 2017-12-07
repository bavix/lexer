<?php

namespace Bavix\Lexer;

use Bavix\Exceptions;

class Lexer
{

    const RAW      = 1;
    const OPERATOR = 2;
    const PRINTER  = 4;
    const LITERAL  = 8;

    /**
     * @var string
     */
    protected $openLiteralRegExp  = "\{%[ \t\n\r \v]*literal[ \t\n\r \v]*%\}";

    /**
     * @var string
     */
    protected $closeLiteralRegExp = "\{%[ \t\n\r \v]*endliteral[ \t\n\r \v]*%\}";

    /**
     * @var array
     */
    protected $literals = [];

    /**
     * @var array
     */
    protected $prints = [
        self::OPERATOR => false,
        self::RAW      => true,
        self::PRINTER  => true,
    ];

    /**
     * @var array
     */
    protected $escaping = [
        self::OPERATOR => false,
        self::RAW      => false,
        self::PRINTER  => true,
    ];

    /**
     * @var array
     */
    protected $phpTags = [
        '<?php' => '<!--',
        '<?='   => '<!--',
        '<?'    => '<!--',
        '?>'    => '-->',
    ];

    protected function last($last, $data, $equal = '.')
    {
        return
            // last exists
            $last &&

            // if exists then type is string?
            in_array($last->type, [\T_STRING, \T_VARIABLE], true) &&

            // if type is string then data is '('?
            $data === $equal &&

            // if true then token is variable ?
            preg_match('~[a-z_]+~i', $last->token);
    }

    protected function analysis(array $tokens)
    {
        $queue = new Queue($tokens);
        $queue->pop(); // remove open <?php

        $open = [
            // open
            '{!' => self::RAW,
            '{%' => self::OPERATOR,
            '{{' => self::PRINTER,
        ];

        $close = [
            // close
            '!}' => self::RAW,
            '%}' => self::OPERATOR,
            '}}' => self::PRINTER,
        ];

        $begin = array_flip($open);

        $end = [
            self::RAW      => '!',
            self::OPERATOR => '%',
            self::PRINTER  => '}',
        ];

        $storage = [
            self::RAW      => [],
            self::OPERATOR => [],
            self::PRINTER  => [],
        ];

        $isOpen = false;
        $iterate = 0;
        $anyType  = null;
        $lastChar = null;
        $type     = null;
        $mixed    = [];
        $last     = null;
        $dot      = null;
        $code     = '';
        $print    = null;

        while (!$queue->isEmpty())
        {
            $read = $queue->pop();

            $_type = Validator::getValue($read);
            $data  = $read[1] ?? $read;

            if ($_type === \T_OPEN_TAG || $_type === \T_OPEN_TAG_WITH_ECHO || $_type === \T_CLOSE_TAG)
            {
                continue;
            }

            if ($type && $_type === \T_INLINE_HTML)
            {
                $lvl = 1;
                $rEnd = $data;

                do {
                    $read = $queue->pop();

                    $_type = Validator::getValue($read);
                    $_data  = $read[1] ?? $read;

                    if ($_type === \T_OPEN_TAG || $_type === \T_OPEN_TAG_WITH_ECHO || $_type === \T_CLOSE_TAG)
                    {
                        continue;
                    }

                    if ($_type === \T_NS_SEPARATOR)
                    {
                        $lvl++;
                    }

                    if ($_data === $rEnd)
                    {
                        $lvl--;
                    }

                    $data .= $_data;

                    if ($queue->isEmpty())
                    {
                        throw new \ParseError('Error code `' . $code . $data . '`');
                    }

                } while ($lvl);
            }

            if ($_type === \T_STRING)
            {
                $isVar = preg_match('~[a-z_]+[\w_]*~i', $data);

                $_type = Validator::getType($data, $isVar ? \T_VARIABLE : \T_STRING, $type);

                if ($isVar && !empty($mixed))
                {
                    $mix = current($mixed);

                    if ($mix->type === \T_FOR && $data === 'in')
                    {
                        $_type = Validator::get('T_FOR_IN');
                    }
                }
            }

            // $i++, --$i, $i += 1, $i.=1...
            $print = $print && !in_array($_type, [
                    \T_INC, // i++, ++i
                    \T_DEC, // i--, --i
                    \T_PLUS_EQUAL, // i+=1
                    \T_MINUS_EQUAL, // i-=1
                    \T_MUL_EQUAL, // i*=1
                    \T_DIV_EQUAL, // i/=1
                    \T_CONCAT_EQUAL, // i.=1
                    \T_SR_EQUAL, // i >>= 1
                    \T_SL_EQUAL, // i <<= 1
                    \T_XOR_EQUAL, // i^=1
                    \T_OR_EQUAL, // i|=1
                    \T_AND_EQUAL, // i&=1
                    \T_MOD_EQUAL, // i%=1
                ], true);

            $code .= $data;

            if ($dot && $anyType === \T_WHITESPACE)
            {
                throw new Exceptions\Runtime('Undefined dot `' . implode(' ', $mixed) . ' ' . $data . '`');
            }

            if ($_type === \T_WHITESPACE)
            {
                $lastChar = $data;
                $anyType  = $_type;
                continue;
            }

            $anyType = $_type;

            if (!$type && $data === '{' && $code !== '{{')
            {
                $code = $data;
            }

            $index = $lastChar . $data;

            if ((!$isOpen && isset($open[$index]) && $type) || (isset($close[$index]) && !$type))
            {
                throw new Exceptions\Logic('Syntax error `' . $lastChar . $data . '`');
            }

            if (!$isOpen && isset($open[$index]))
            {
                if ($dot)
                {
                    throw new Exceptions\Runtime('Undefined dot');
                }

                $isOpen = true;
                $type  = $open[$lastChar . $data];
                $print = $this->prints[$type];
            }
            else if (isset($close[$index]))
            {
                if ($dot)
                {
                    throw new Exceptions\Runtime('Undefined dot `' . \implode(' ', $mixed) . '`');
                }

                if ($type !== $close[$lastChar . $data])
                {
                    throw new Exceptions\Runtime(
                        'Undefined syntax code `' . $begin[$type] . ' ' . \implode(' ', $mixed) . $data . '`');
                }

                if (empty($mixed))
                {
                    throw new Exceptions\Blank('Empty tokens `' . $code . '`');
                }

                $token    = current($mixed);
                $name     = $token->name;
                $fragment = \preg_replace('~[ \t\n\r\v]{2,}~', ' ', $code);

                $storage[$type][] = [
                    'type'     => $type,
                    'print'    => $print,
                    'escape'   => $this->escaping[$type],
                    'name'     => $name,
                    'code'     => $code,
                    'fragment' => \trim(\mb_substr($fragment, 2, -2)),
                    'tokens'   => $mixed
                ];

                $isOpen = false;
                $mixed = [];
                $type  = null;
                $last  = null;
                $code  = '';
            }
            else if ($type)
            {
                if ($end[$type] !== $data)
                {
                    if ($this->last($last, $data, '('))
                    {
                        $last->type = \T_FUNCTION;
                    }
                    else if ($this->last($last, $data, '.') || $dot)
                    {
                        $dot         = !$dot;
                        $last->token .= $data;

                        continue;
                    }

                    $mixed[] = $last = new Token($data, $_type);
                }
                else
                {
                    $_next = $queue->next();

                    if ($end[$type] === $data && $_next)
                    {
                        $_nextToken = $_next[1] ?? $_next;

                        if ($_nextToken !== '}')
                        {
                            $mixed[] = $last = new Token($data, $_type);
                        }
                    }
                }
            }

            $lastChar = $data;
            $iterate++;
        }

        // set literal & cleanup literals
        $storage[self::LITERAL] = $this->literals;
        $this->literals         = [];

        return $storage;
    }

    /**
     * @param array $matches
     *
     * @return string
     */
    protected function literal(array $matches)
    {
        // hash from matches
        $hash = '[!' . __FUNCTION__ . '::read(' . \crc32($matches[1]) . ')!]';

        // save hash and value to literals array
        $this->literals[$hash] = $matches[1];

        // return hash value for replace
        return $hash;
    }

    /**
     * @param string $source
     *
     * @return array
     */
    public function tokens(&$source)
    {
        // literal from source to array
        $source = \preg_replace_callback(
            "~{$this->openLiteralRegExp}(\X*?){$this->closeLiteralRegExp}~u",
            [$this, 'literal'],
            $source
        );

        // if check literal open then throw
        if (\preg_match("~{$this->openLiteralRegExp}~u", $source))
        {
            throw new Exceptions\Logic('Literal isn\'t closed');
        }

        // if check literal close then throw
        if (\preg_match("~{$this->closeLiteralRegExp}~u", $source))
        {
            throw new Exceptions\Logic('Literal isn\'t open');
        }

        // remove comments
        $source  = \preg_replace('~\{(?<q>\*)\X*?(\k<q>)\}~u', '', $source);
        $source  = \strtr($source, $this->phpTags); // remove php tags
        $lexCode = \preg_replace('~("|\'|#|\/{2}|\/\*)~u', '?>$1<?php ', $source);

        // analysis tokens
        return $this->analysis(
        // source progress with helped tokenizer
            \token_get_all('<?php' . PHP_EOL . $lexCode)
        );
    }

}
