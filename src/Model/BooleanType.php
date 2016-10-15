<?php

namespace ZF\Apigility\Documentation\Swagger\Model;

class BooleanType implements Type
{

    public function match($target)
    {
        return is_bool($target);
    }

    public function generate($target)
    {
        return [
            'type' => 'boolean'
        ];
    }
}
