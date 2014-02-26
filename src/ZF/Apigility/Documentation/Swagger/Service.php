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

        // parameters
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

        $alwaysPresentResponses = array(
            406 => array(
                'code' => 406,
                'message' => 'Invalid Accept header'
            ),
            415 => array(
                'code' => 406,
                'message' => 'Invalid content-type header'
            ),
        );

        $okResponses = array(
            200 => array(
                'code' => 200,
                'message' => 'Success',
            ),
        );

        $notFoundResponses = array(
            404 => array(
                'code' => 404,
                'message' => 'The ' . $service->api->getName() . ' cannot be found'
            ),
        );

        $validationResponses = array(
            422 => array(
                'code' => 422,
                'message' => 'Failed validation of the ' . $service->api->getName() . ' model'
            )
        );

        $authResponses = array(
            401 => array(
                'code' => 401,
                'message' => 'Invalid Credentials',
            ),
            403 => array(
                'code' => 403,
                'message' => 'Forbidden',
            ),
        );

        $deleteResponses = array(
            204 => array(
                'code' => 204,
                'message' => 'No Content',
            ),
        );

        $operationGroups = array();

        // if there is a routeIdentifierName, this is REST service, need to enumerate
        if ($service->routeIdentifierName) {
            $fields = $service->fields;

            // find all ENTITY operations
            $entityOperations = array();
            foreach ($service->entityOperations as $entityOperation) {
                $method           = $entityOperation->getHttpMethod();
                $responseMessages = $alwaysPresentResponses + $notFoundResponses;
                $entityParameters = array_values($templateParameters);

                if (in_array($method, array('POST', 'PUT', 'PATCH'))) {
                    $entityParameters[] = $postPatchPutBodyParameter;
                    $responseMessages += $okResponses;

                    if (! empty($fields)) {
                        $responseMessages += $validationResponses;
                    }
                } elseif ($method === 'GET') {
                    $responseMessages += $okResponses;
                } elseif ($method === 'DELETE') {
                    $responseMessages += $deleteResponses;
                }

                if ($entityOperation->requiresAuthorization()) {
                    $responseMessages += $authResponses;
                }

                $entityOperations[] = array(
                    'method'           => $method,
                    'summary'          => $entityOperation->getDescription(),
                    'notes'            => $entityOperation->getDescription(),
                    'nickname'         => $method . ' for ' . $service->api->getName(),
                    'type'             => $service->api->getName(),
                    'parameters'       => $entityParameters,
                    'responseMessages' => array_values($responseMessages)
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
                $method               = $operation->getHttpMethod();
                $responseMessages     = $alwaysPresentResponses;
                $collectionParameters = array_values($templateParameters);

                if (in_array($method, array('POST', 'PUT', 'PATCH'))) {
                    $collectionParameters[] = $postPatchPutBodyParameter;
                    $responseMessages += $okResponses;

                    if (! empty($fields)) {
                        $responseMessages += $validationResponses;
                    }
                } elseif ($method === 'GET') {
                    $responseMessages += $okResponses;
                } elseif ($method === 'DELETE') {
                    $responseMessages += $deleteResponses;
                }

                if ($operation->requiresAuthorization()) {
                    $responseMessages += $authResponses;
                }

                $operations[] = array(
                    'method'           => $method,
                    'summary'          => $operation->getDescription(),
                    'notes'            => $operation->getDescription(),
                    'nickname'         => $method . ' for ' . $service->api->getName(),
                    'type'             => $service->api->getName(),
                    'parameters'       => $collectionParameters,
                    'responseMessages' => array_values($responseMessages)
                );
            }
            $operationGroups[] = array(
                'operations' => $operations,
                'path'       => str_replace('/{' . $service->routeIdentifierName . '}', '', $routeWithReplacements)
            );
        } else {
            $fields = $service->fields;

            // find all other operations
            $operations = array();
            foreach ($service->operations as $operation) {
                $method           = $operation->getHttpMethod();
                $responseMessages = $alwaysPresentResponses;
                $parameters       = array_values($templateParameters);

                if (in_array($method, array('POST', 'PUT', 'PATCH'))) {
                    $parameters[] = $postPatchPutBodyParameter;
                    $responseMessages += $okResponses;

                    if (! empty($fields)) {
                        $responseMessages += $validationResponses;
                    }
                } elseif ($method === 'GET') {
                    $responseMessages += $okResponses;
                } elseif ($method === 'DELETE') {
                    $responseMessages += $deleteResponses;
                }

                if ($operation->requiresAuthorization()) {
                    $responseMessages += $authResponses;
                }

                $operations[] = array(
                    'method'           => $method,
                    'summary'          => $operation->getDescription(),
                    'notes'            => $operation->getDescription(),
                    'nickname'         => $method . ' for ' . $service->api->getName(),
                    'type'             => $service->api->getName(),
                    'parameters'       => $parameters,
                    'responseMessages' => array_values($responseMessages)
                );
            }
            $operationGroups[] = array(
                'operations' => $operations,
                'path'       => $routeWithReplacements
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
            'apiVersion'     => $service->api->getVersion(),
            'swaggerVersion' => '1.2',
            'basePath'       => $this->baseUrl,
            'resourcePath'   => $routeBasePath,
            'apis'           => $operationGroups,
            'produces'       => $service->requestAcceptTypes,
            'models'         => array(
                $service->api->getName() => array(
                    'id'         => $service->api->getName(),
                    'required'   => $requiredProperties,
                    'properties' => $properties,
                ),
            ),
        );
    }
}
