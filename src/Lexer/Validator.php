<?php

namespace Bavix\Lexer;

class Validator
{

    const T_EQUAL      = 100000;
    const T_FOR_IN     = self::T_EQUAL - 1;
    const T_NULL       = self::T_FOR_IN - 1;
    const T_BRACKET    = self::T_NULL - 1;
    const T_ENDBRACKET = self::T_BRACKET - 1;
    const T_ENDARRAY   = self::T_ENDBRACKET - 1;

    const T_HELPER    = self::T_ENDARRAY - 1;
    const T_ENDHELPER = self::T_HELPER - 1;

    const T_WITH    = self::T_ENDHELPER - 1;
    const T_ENDWITH = self::T_WITH - 1;

    const T_FORELSE = self::T_ENDWITH - 1;

    const T_DOT = self::T_FORELSE - 1;
    const T_AT  = self::T_DOT - 1;

    const T_CONCAT = self::T_AT - 1;
    const T_COMMA = self::T_CONCAT- 1;

    const T_QUESTION_MARK    = self::T_COMMA - 1;
    const T_EXCLAMATION_MARK = self::T_QUESTION_MARK - 1;

    const T_MOD = self::T_EXCLAMATION_MARK - 1;

    protected static $globalTypes = [
        '['    => \T_ARRAY,
        ']'    => self::T_ENDARRAY,
        '='    => self::T_EQUAL,
        'null' => self::T_NULL,
        '('    => self::T_BRACKET,
        ')'    => self::T_ENDBRACKET,
        '~'    => self::T_CONCAT,
        ','    => self::T_COMMA,
        '.'    => self::T_DOT,
        '@'    => self::T_AT,
        '!'    => self::T_EXCLAMATION_MARK,
        '?'    => self::T_QUESTION_MARK,
        '%'    => self::T_QUESTION_MARK,
    ];

    protected static $lexerTypes = [
        Lexer::OPERATOR => [
            'helper'    => self::T_HELPER,
            'endhelper' => self::T_ENDHELPER,

            'with'    => self::T_WITH,
            'endwith' => self::T_ENDWITH,

            'forelse' => self::T_FORELSE,
        ],
    ];

    /**
     * @param array|string $type
     *
     * @return int|string
     */
    public static function getValue($type)
    {

        if (!is_array($type))
        {
            return \T_STRING;
        }

        return $type[0];
    }

    /**
     * @return array
     */
    protected static function constants()
    {
        static $_;

        if (!$_)
        {
            $ref = new \ReflectionClass(static::class);
            $_   = $ref->getConstants();
        }

        return $_;
    }

    public static function getType($value, $default, $lexerType)
    {
        if ($lexerType)
        {
            if (isset(static::$lexerTypes[$lexerType][$value]))
            {
                return static::$lexerTypes[$lexerType][$value];
            }
        }

        if (isset(static::$globalTypes[$value]))
        {
            return static::$globalTypes[$value];
        }

        return $default;
    }

    /**
     * @param $type
     *
     * @return int|string
     */
    public static function get($type)
    {
        if (\is_string($type))
        {
            if (\defined(static::class . '::' . $type))
            {
                return \constant(static::class . '::' . $type);
            }

            if (\defined($type))
            {
                return \constant($type);
            }

            return \T_STRING;
        }

        foreach (static::constants() as $name => $value)
        {
            if ($value === $type)
            {
                return $name;
            }
        }

        $token = \token_name($type);

        if ($token === 'UNKNOWN')
        {
            return 'T_STRING';
        }

        return $token;
    }

}
