<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Apigility\Documentation\ApiFactory;

class SwaggerUiControllerFactory implements FactoryInterface
{
    /**
     * Create and return a SwaggerUiController instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return SwaggerUiController
     * @throws ServiceNotCreatedException when ApiFactory service is missing.
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (! $container->has(ApiFactory::class)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires the service %s, which was not found',
                SwaggerUiController::class,
                ApiFactory::class
            ));
        }

        return new SwaggerUiController($container->get(ApiFactory::class));
    }

    /**
     * Create and return a SwaggerUiController instance.
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $controllers
     * @return SwaggerUiController
     * @throws ServiceNotCreatedException when ApiFactory service is missing.
     */
    public function createService(ServiceLocatorInterface $controllers)
    {
        $container = $controllers->getServiceLocator() ?: $controllers;
        return $this($container, SwaggerUiController::class);
    }
}
