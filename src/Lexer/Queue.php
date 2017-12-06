<?php

namespace Bavix\Lexer;

class Queue extends \Bavix\Foundation\Arrays\Queue
{

    /**
     * @return mixed|null
     */
    public function next()
    {
        $self = clone $this;
        
        return $self->isEmpty() ? 
            null : 
            $self->pop();
    }
    
}
