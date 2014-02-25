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

        // routes and parameter mangling ([:foo] will become {foo}
        $routeBasePath = substr($service->route, 0, strpos($service->route, '['));
        $routeWithReplacements = str_replace(array('[', ']', '{/', '{:'), array('{', '}', '/{', '{'), $service->route);

        // find all parameters in Swagger naming format
        preg_match_all('#{([\w\d_-]+)}#', $routeWithReplacements, $parameterMatches);

        $templateParameters = array();
        foreach ($parameterMatches[1] as $paramSegmentName) {
            $templateParameters[$paramSegmentName] = array(
                'paramType' => 'path',
                'name' => $paramSegmentName,
                'description' => 'URL parameter ' . $paramSegmentName,
                'dataType' => 'string',
                'required' => false,
                'minimum' => 0,
                'maximum' => 1
            );
        }

        $postPatchPutBodyParameter = array(
            'name' => 'body',
            'paramType' => 'body',
            'required' => true,
            'type' => $service->api->getName()
        );

        $operationGroups = array();

        // if there is a routeIdentifierName, this is REST service, need to enumerate
        if ($service->routeIdentifierName) {
            // find all ENTITY operations
            $entityOperations = array();
            foreach ($service->entityOperations as $entityOperation) {
                $method = $entityOperation->getHttpMethod();
                $entityParameters = array_values($templateParameters);
                if (in_array($method, array('POST', 'PUT', 'PATCH'))) {
                    $entityParameters[] = $postPatchPutBodyParameter;
                }
                $entityOperations[] = array(
                    'method' => $method,
                    'notes' => $entityOperation->getDescription(),
                    'nickname' => $method . ' for ' . $service->api->getName(),
                    'type' => $service->api->getName(),
                    'parameters' => $entityParameters
                );
            }
            $operationGroups[] = array(
                'operations' => $entityOperations,
                'path' => $routeWithReplacements
            );

            // find all COLLECTION operations
            $operations = array();
            foreach ($service->operations as $operation) {
                unset($templateParameters[$service->routeIdentifierName]);
                $method = $operation->getHttpMethod();
                $collectionParameters = array_values($templateParameters);
                if (in_array($method, array('POST', 'PUT', 'PATCH'))) {
                    $collectionParameters[] = $postPatchPutBodyParameter;
                }
                $operations[] = array(
                    'method' => $method,
                    'notes' => $operation->getDescription(),
                    'nickname' => $method . ' for ' . $service->api->getName(),
                    'type' => $service->api->getName(),
                    'parameters' => $collectionParameters
                );
            }
            $operationGroups[] = array(
                'operations' => $operations,
                'path' => str_replace('/{' . $service->routeIdentifierName . '}', '', $routeWithReplacements)
            );
        } else {
            // find all other operations
            $operations = array();
            foreach ($service->operations as $operation) {
                $method = $operation->getHttpMethod();
                $parameters = array_values($templateParameters);
                if (in_array($method, array('POST', 'PUT', 'PATCH'))) {
                    $parameters[] = $postPatchPutBodyParameter;
                }
                $operations[] = array(
                    'method' => $method,
                    'notes' => $operation->getDescription(),
                    'nickname' => $method . ' for ' . $service->api->getName(),
                    'type' => $service->api->getName(),
                    'parameters' => $parameters
                );
            }
            $operationGroups[] = array(
                'operations' => $operations,
                'path' => $routeWithReplacements
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

        return array(
            'apiVersion' => $service->api->getVersion(),
            'swaggerVersion' => '1.2',
            'basePath' => $this->baseUrl,
            'resourcePath' => $routeBasePath,
            'apis' => $operationGroups,
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
