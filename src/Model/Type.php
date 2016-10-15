<?php

namespace ZF\Apigility\Documentation\Swagger\Model;

interface Type
{
    public function match($target);

    public function generate($target);
}
