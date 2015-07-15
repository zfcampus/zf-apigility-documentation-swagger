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
        $routeWithReplacements = str_replace(['[', ']', '{/', '{:'], ['{', '}', '/{', '{'], $service->route);

        // find all parameters in Swagger naming format
        preg_match_all('#{([\w\d_-]+)}#', $routeWithReplacements, $parameterMatches);

        // parameters
        $templateParameters = [];
        foreach ($parameterMatches[1] as $paramSegmentName) {
            $templateParameters[$paramSegmentName] = [
                'paramType' => 'path',
                'name' => $paramSegmentName,
                'description' => 'URL parameter ' . $paramSegmentName,
                'dataType' => 'string',
                'required' => false,
                'minimum' => 0,
                'maximum' => 1
            ];
        }

        $postPatchPutBodyParameter = [
            'name' => 'body',
            'paramType' => 'body',
            'required' => true,
            'type' => $service->getName()
        ];

        $operationGroups = [];

        // if there is a routeIdentifierName, this is REST service, need to enumerate
        if ($service->routeIdentifierName) {
            $entityOperations = [];
            $collectionOperations = [];

            // find all COLLECTION operations
            foreach ($service->operations as $collectionOperation) {
                $method               = $collectionOperation->getHttpMethod();

                // collection parameters
                $collectionParameters = $templateParameters;
                unset($collectionParameters[$service->routeIdentifierName]);
                $collectionParameters = array_values($collectionParameters);

                if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                    $collectionParameters[] = $postPatchPutBodyParameter;
                }

                $collectionOperations[] = [
                    'method'           => $method,
                    'summary'          => $collectionOperation->getDescription(),
                    'notes'            => $collectionOperation->getDescription(),
                    'nickname'         => $method . ' for ' . $service->getName(),
                    'type'             => $service->getName(),
                    'parameters'       => $collectionParameters,
                    'responseMessages' => $collectionOperation->getResponseStatusCodes(),
                ];
            }

            // find all ENTITY operations
            foreach ($service->entityOperations as $entityOperation) {
                $method           = $entityOperation->getHttpMethod();
                $entityParameters = array_values($templateParameters);

                if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                    $entityParameters[] = $postPatchPutBodyParameter;
                }

                $entityOperations[] = [
                    'method'           => $method,
                    'summary'          => $entityOperation->getDescription(),
                    'notes'            => $entityOperation->getDescription(),
                    'nickname'         => $method . ' for ' . $service->getName(),
                    'type'             => $service->getName(),
                    'parameters'       => $entityParameters,
                    'responseMessages' => $entityOperation->getResponseStatusCodes(),
                ];
            }

            $operationGroups[] = [
                'operations' => $collectionOperations,
                'path'       => str_replace('/{' . $service->routeIdentifierName . '}', '', $routeWithReplacements)
            ];

            $operationGroups[] = [
                'operations' => $entityOperations,
                'path' => $routeWithReplacements
            ];
        } else {
            // find all other operations
            $operations = [];
            foreach ($service->operations as $operation) {
                $method           = $operation->getHttpMethod();
                $parameters       = array_values($templateParameters);

                if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                    $parameters[] = $postPatchPutBodyParameter;
                }

                $operations[] = [
                    'method'           => $method,
                    'summary'          => $operation->getDescription(),
                    'notes'            => $operation->getDescription(),
                    'nickname'         => $method . ' for ' . $service->getName(),
                    'type'             => $service->getName(),
                    'parameters'       => $parameters,
                    'responseMessages' => $operation->getResponseStatusCodes(),
                ];
            }
            $operationGroups[] = [
                'operations' => $operations,
                'path'       => $routeWithReplacements
            ];
        }

        // Fields are part of the default input filter when present.
        $fields = $service->fields;
        if (isset($fields['input_filter'])) {
            $fields = $fields['input_filter'];
        }

        $requiredProperties = $properties = [];
        foreach ($fields as $field) {
            $properties[$field->getName()] = [
                'type' => method_exists($field, 'getType') ? $field->getType() : 'string',
                'description' => $field->getDescription()
            ];
            if ($field->isRequired()) {
                $requiredProperties[] = $field->getName();
            }
        }

        return [
            'apiVersion'     => $service->api->getVersion(),
            'swaggerVersion' => '1.2',
            'basePath'       => $this->baseUrl,
            'resourcePath'   => $routeBasePath,
            'apis'           => $operationGroups,
            'produces'       => $service->requestAcceptTypes,
            'models'         => [
                $service->getName() => [
                    'id'         => $service->getName(),
                    'required'   => $requiredProperties,
                    'properties' => $properties,
                ],
            ],
        ];
    }
}
