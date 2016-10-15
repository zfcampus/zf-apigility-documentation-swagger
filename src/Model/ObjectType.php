<?php

namespace ZF\Apigility\Documentation\Swagger\Model;

class ObjectType implements Type
{

    protected $modelGenerator;

    public function __construct(ModelGenerator $modelGenerator)
    {
        $this->modelGenerator = $modelGenerator;
    }

    public function match($target)
    {
        return is_object($target);
    }

    public function generate($target)
    {
        return [
            'type' => 'object',
            'properties' => array_reduce(
                array_keys(get_object_vars($target)),
                function (&$acc, $key) use ($target) {
                    return array_merge(
                        [$key => $this->modelGenerator->generateType($target->$key)],
                        $acc
                    );
                },
                []
            )
        ];
    }
}
