<?php

namespace ZF\Apigility\Documentation\Swagger\Model;

class ModelGenerator
{

    protected $types;

    public function __construct()
    {
        $this->types = [
            new ObjectType($this),
            new NumberType(),
            new IntegerType(),
            new StringType(),
            new BooleanType(),
            new ArrayType($this),
        ];
    }

    public function generate($jsonInput)
    {
        $target = json_decode($jsonInput);
        if (!$target) {
            return false;
        }
        return array_merge(
            $this->generateType($target),
            ['example' => json_decode($jsonInput, true)]
        );
    }

    public function generateType($target)
    {
        foreach ($this->types as $type) {
            if ($type->match($target)) {
                return $type->generate($target);
            }
        }
    }
}
