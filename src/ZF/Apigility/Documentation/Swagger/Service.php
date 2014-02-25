<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger;

use ZF\Apigility\Documentation\Service as BaseService;

class Service extends BaseService
{
    /**
     * @var BaseService
     */
    protected $service;

    /**
     * @param BaseService $service 
     * @param string $baseUrl 
     */
    public function __construct(BaseService $service, $baseUrl)
    {
        $this->service = $service;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        // localize service object for brevity
        $service = $this->service;

        // routes and parameter mangling
        $routeBasePath = substr($service->route, 0, strpos($service->route, '['));
        $routeWithReplacements = str_replace(array('[', ']', '{/', '{:'), array('{', '}', '/{', '{'), $service->route);

        // find all parameters in Swagger naming format
        preg_match_all('#{([\w\d_-]+)}#', $routeWithReplacements, $parameterMatches);

        $parameters = array();
        foreach ($parameterMatches[1] as $paramSegmentName) {
            $parameters[] = array(
                'paramType' => 'path',
                'name' => $paramSegmentName,
                'description' => 'URL parameter ' . $paramSegmentName,
                'dataType' => 'string',
                'required' => false,
                'minimum' => 0,
                'maximum' => 1
            );
        }

        // find all operations
        $operations = array();
        foreach ($service->operations as $operation) {
            $method = $operation->getHttpMethod();
            $operations[] = array(
                'method' => $method,
                'notes' => $operation->getDescription(),
                'nickname' => $method . ' for ' . $service->api->getName(),
                'type' => $service->api->getName(),
                'parameters' => $parameters
            );
        }

        $requiredProperties = $properties = array();
        foreach ($service->fields as $field) {
            $properties[$field->getName()] = array(
                'type' => 'string',
                'description' => $field->getDescription()
            );
            if ($field->isRequired()) {
                $requiredProperties[] = $field->getName();
            }
        }



//        "parameters":[
//          {
//              "paramType": "path",
//            "name": "petId",
//            "description": "ID of pet that needs to be fetched",
//            "dataType": "integer",
//            "format": "int64",
//            "required": true,
//            "minimum": 0,
//            "maximum": 10
//          }
//        ],

        return array(
            'apiVersion' => $service->api->getVersion(),
            'swaggerVersion' => '1.2',
            'basePath' => $this->baseUrl,
            'resourcePath' => $routeBasePath,
            'apis' => array(array(
                'operations' => $operations,
                'path' => $routeWithReplacements,
            )),
            'produces' => $service->requestAcceptTypes,
            'models' => array(
                $service->api->getName() => array(
                    'id' => $service->api->getName(),
                    'required' => $requiredProperties,
                    'properties' => $properties,
                ),
            ),
        );
    }
}
