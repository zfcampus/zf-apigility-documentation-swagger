<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger\Exception;

use RuntimeException;

class UnmatchedTypeException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param mixed $type
     * @return self
     */
    public static function forType($type)
    {
        return new self(sprintf(
            'Unable to generate type for value (%s); perhaps the value is invalid?',
            is_object($type) ? get_class($type) : var_export($type, true)
        ));
    }
}
