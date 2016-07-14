<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger;

use Interop\Container\ContainerInterface;

class SwaggerViewStrategyFactory
{
    /**
     * @param ContainerInterface $container
     * @return SwaggerViewStrategy
     */
    public function __invoke(ContainerInterface $container)
    {
        return new SwaggerViewStrategy($container->get('ViewJsonRenderer'));
    }
}
