<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Documentation\Swagger;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\ServiceManager;
use ZF\Apigility\Documentation\ApiFactory;
use ZF\Apigility\Documentation\Swagger\SwaggerUiController;
use ZF\Apigility\Documentation\Swagger\SwaggerUiControllerFactory;
use ZF\Apigility\Provider\ApigilityProviderInterface;
use ZF\Configuration\ModuleUtils;

class SwaggerUiControllerFactoryTest extends TestCase
{
    /**
     * @var SwaggerUiControllerFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $services;

    protected function setUp()
    {
        $this->factory = new SwaggerUiControllerFactory();
        $this->services = $services = new ServiceManager();

        parent::setUp();
    }

    /**
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotCreatedException
     */
    public function testExceptionThrownOnMissingApiCreatorClass()
    {
        $smFactory = $this->factory;
        $this->setExpectedException(ServiceNotCreatedException::class);
        $factory = $smFactory($this->services, SwaggerUiController::class);
    }

    public function testCreatesServiceWithDefaults()
    {
        $mockModule = $this->prophesize(ApigilityProviderInterface::class)->reveal();

        $moduleManager = $this->prophesize(ModuleManager::class);
        $moduleManager->getModules()->willReturn(['Test']);
        $moduleManager->getModule('Test')->willReturn($mockModule);
        $moduleUtils = $this->prophesize(ModuleUtils::class);
        $moduleUtils
            ->getModuleConfigPath('Test')
            ->willReturn([]);

        $apiFactory = new ApiFactory(
            $moduleManager->reveal(),
            [],
            $moduleUtils->reveal()
        );

        $this->services->setService(ApiFactory::class, $apiFactory);

        /** @var SwaggerUiControllerFactory $service */
        $smFactory = $this->factory;
        $this->assertInstanceOf(SwaggerUiControllerFactory::class, $smFactory);

        $controller = $smFactory($this->services, SwaggerUiController::class);
        $this->assertInstanceOf(SwaggerUiController::class, $controller);
    }
}
