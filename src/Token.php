<?php

namespace Bavix\Lexer;

use Bavix\Iterator\Traits\JsonSerializable;

/**
 * Class Token
 *
 * @package Bavix\Lexer
 *
 * @property string $token
 * @property string $name
 * @property int $type
 */
class Token implements \JsonSerializable
{

    use JsonSerializable;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Token constructor.
     *
     * @param string $data
     * @param string $type
     */
    public function __construct($data, $type)
    {
        $this->data['token'] = $data;
        $this->data['type'] = $type;
        $this->data['name'] = Validator::get($type);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->data['token'];
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        if ($name === 'type') {
            $this->data['name'] = Validator::get($value);
        }

        if ($name === 'name') {
            $this->data['type'] = Validator::get($value);
        }

        $this->data[$name] = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return $this->data;
    }

}
