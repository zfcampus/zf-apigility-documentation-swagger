<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger\Model;

interface TypeInterface
{
    /**
     * @param mixed $target Value to attempt to match to the given type.
     * @return bool
     */
    public function match($target);

    /**
     * @param mixed $target Value to generate documentation for.
     * @return array Specification for the given type.
     */
    public function generate($target);
}
