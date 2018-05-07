<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger\Model;

use ZF\Apigility\Documentation\Swagger\Exception\UnmatchedTypeException;

class ObjectType implements TypeInterface
{
    private $modelGenerator;

    public function __construct(ModelGenerator $modelGenerator)
    {
        $this->modelGenerator = $modelGenerator;
    }

    /**
     * {@inheritDoc}
     */
    public function match($target)
    {
        return is_object($target);
    }

    /**
     * {@inheritDoc}
     * @throws UnmatchedTypeException if any given property cannot be resolved
     *     to a known type.
     */
    public function generate($target)
    {
        return [
            'type' => 'object',
            'properties' => array_reduce(
                array_keys(get_object_vars($target)),
                function ($types, $key) use ($target) {
                    return array_merge(
                        $types,
                        [$key => $this->modelGenerator->generateType($target->$key)]
                    );
                },
                []
            )
        ];
    }
}
