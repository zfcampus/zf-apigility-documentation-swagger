<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger;

use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SwaggerUiControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllers
     * @return SwaggerUiController
     * @throws ServiceNotCreatedException if the ZF\Apigility\Documentation\ApiFactory service is missing
     */
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator();
        if (!$services->has('ZF\Apigility\Documentation\ApiFactory')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s\SwaggerUiController requires the service ZF\Apigility\Documentation\ApiFactory, '
                . 'which was not found',
                __NAMESPACE__
            ));
        }
        return new SwaggerUiController($services->get('ZF\Apigility\Documentation\ApiFactory'));
    }
}
