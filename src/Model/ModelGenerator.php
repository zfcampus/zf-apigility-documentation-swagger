<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger\Model;

use ZF\Apigility\Documentation\Swagger\Exception\UnmatchedTypeException;

class ModelGenerator
{
    private $types;

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

    /**
     * @param string $jsonInput
     * @return array
     * @throws UnmatchedTypeException if unable to match any given $target to a
     *     known type.
     */
    public function generate($jsonInput)
    {
        $target = json_decode($jsonInput);

        if (! $target) {
            return false;
        }

        return array_merge(
            $this->generateType($target),
            ['example' => json_decode($jsonInput, true)]
        );
    }

    /**
     * @param mixed $target
     * @return TypeInterface
     * @throws UnmatchedTypeException if unable to match $target to a known type.
     */
    public function generateType($target)
    {
        foreach ($this->types as $type) {
            if ($type->match($target)) {
                return $type->generate($target);
            }
        }

        throw UnmatchedTypeException::forType($target);
    }
}
