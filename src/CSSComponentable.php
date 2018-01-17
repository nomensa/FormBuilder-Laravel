<?php

namespace Nomensa\FormBuilder;

trait CSSComponentable
{

    public function __call($method, $parameters)
    {
        var_dump($method);
        var_dump($parameters);

        if (static::hasComponent($method)) {
            return $this->componentCall($method, $parameters);
        }

        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        throw new BadMethodCallException("Method {$method} does not exist.");
    }

}
