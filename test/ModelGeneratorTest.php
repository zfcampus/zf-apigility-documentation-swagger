<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2018 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Documentation\Swagger\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Documentation\Swagger\Model\ModelGenerator;

class ModelGeneratorTest extends TestCase
{
    /**
     * @var string
     */
    protected $fixturesPath = __DIR__ . '/TestAsset/fixtures/models/';

    /**
     * @var ModelGenerator
     */
    protected $modelGenerator;

    public function setUp()
    {
        $this->modelGenerator = new ModelGenerator();
    }

    private function getFixtureData($inputFileName, $resultFileName)
    {
        $inputPath = $this->fixturesPath . $inputFileName;
        $resultPath = $this->fixturesPath . $resultFileName;
        return [
            file_get_contents($inputPath),
            json_decode(file_get_contents($resultPath), true)
        ];
    }

    public function testShouldBeCreated()
    {
        $this->assertNotNull($this->modelGenerator);
    }

    public function generateInvalidInputDataProvider()
    {
        return [
            ['adfadfadf'],
            [''],
            [null],
            ['{']
        ];
    }

    /**
     * @dataProvider generateInvalidInputDataProvider
     */
    public function testShouldReturnsFalseWithAnInvalidJsonInput($input)
    {
        $swaggerModel = $this->modelGenerator->generate($input);
        $this->assertFalse($swaggerModel);
    }

    public function generateDataProvider()
    {
        return [
            $this->getFixtureData('types-input.json', 'types-result.json'),
            $this->getFixtureData('nested-input.json', 'nested-result.json'),
            $this->getFixtureData('hal-input.json', 'hal-result.json')
        ];
    }

    /**
     * @dataProvider generateDataProvider
     */
    public function testShouldGenerateASwaggerModel($input, $expectedModel)
    {
        $swaggerModel = $this->modelGenerator->generate($input);
        $this->assertEquals($expectedModel, $swaggerModel);
    }
}
