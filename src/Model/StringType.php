<?php

namespace ZF\Apigility\Documentation\Swagger\Model;

class StringType implements Type
{

    public function match($target)
    {
        return is_string($target);
    }

    public function generate($target)
    {
        return [
            'type' => 'string'
        ];
    }
}
