<?php

namespace ZF\Apigility\Documentation\Swagger\Model;

class IntegerType implements Type
{

    public function match($target)
    {
        return is_int($target);
    }

    public function generate($target)
    {
        return [
            'type' => 'integer'
        ];
    }
}
