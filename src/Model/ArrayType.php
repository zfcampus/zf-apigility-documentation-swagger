<?php

namespace ZF\Apigility\Documentation\Swagger\Model;

class ArrayType implements Type
{

    protected $modelGenerator;

    public function __construct(ModelGenerator $modelGenerator)
    {
        $this->modelGenerator = $modelGenerator;
    }

    public function match($target)
    {
        return is_array($target);
    }

    public function generate($target)
    {
        return [
            'type' => 'array',
            'items' => $this->modelGenerator->generateType($target[0])
        ];
    }
}
