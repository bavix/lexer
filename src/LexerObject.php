<?php

namespace Bavix\Lexer;

class LexerObject
{

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $lexemes;

    /**
     * LexerObject constructor.
     * @param string $source
     * @param string $template
     * @param array $lexemes
     */
    public function __construct(string $source, string $template, array $lexemes)
    {
        $this->source = $source;
        $this->template = $template;
        $this->lexemes = $lexemes;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return array
     */
    public function getLexemes(): array
    {
        return $this->lexemes;
    }

}
