<?php

namespace ZF\Apigility\Documentation\Swagger\Model;

class NumberType implements Type
{

    public function match($target)
    {
        return is_float($target);
    }

    public function generate($target)
    {
        return [
            'type' => 'number'
        ];
    }
}
