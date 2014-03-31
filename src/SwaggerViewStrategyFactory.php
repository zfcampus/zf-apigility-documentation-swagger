<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger;

class SwaggerViewStrategyFactory
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $services
     * @return SwaggerViewStrategy
     */
    public function __invoke($services)
    {
        return new SwaggerViewStrategy($services->get('ViewJsonRenderer'));
    }
}
