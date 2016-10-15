<?php

namespace ZFTest\Apigility\Documentation\Swagger;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\ModuleManager\ModuleManager;
use ZF\Apigility\Documentation\ApiFactory;
use ZF\Apigility\Provider\ApigilityProviderInterface;
use ZF\Configuration\ModuleUtils;
use ZF\Apigility\Documentation\Swagger\Api;

abstract class BaseApiFactoryTest extends TestCase
{

    /**
     * @var ApiFactory
     */
    protected $apiFactory;

    public function setUp()
    {
        $mockModule = $this->prophesize(ApigilityProviderInterface::class)->reveal();

        $moduleManager = $this->prophesize(ModuleManager::class);
        $moduleManager->getModules()->willReturn(['Test']);
        $moduleManager->getModule('Test')->willReturn($mockModule);

        $moduleUtils = $this->prophesize(ModuleUtils::class);
        $moduleUtils
            ->getModuleConfigPath('Test')
            ->willReturn(__DIR__ . '/TestAsset/module-config/module.config.php');

        $this->apiFactory = new ApiFactory(
            $moduleManager->reveal(),
            include __DIR__ . '/TestAsset/module-config/module.config.php',
            $moduleUtils->reveal()
        );
        $this->api = new Api($this->apiFactory->createApi('Test', 1));
        parent::setUp();
    }

    protected function getFixture($fixtureFilename)
    {
        $fixturePath = dirname(__FILE__) . '/TestAsset/fixtures/';
        $fixture = file_get_contents($fixturePath . $fixtureFilename);
        return json_decode($fixture, true);
    }

    protected function assertFixture($fixtureFilename, $value)
    {
        $expectedValue = $this->getFixture($fixtureFilename);
        $this->assertEquals($expectedValue, $value);
    }
}
