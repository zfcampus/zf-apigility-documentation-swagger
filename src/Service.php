<?php

/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2018 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger;

use ZF\Apigility\Documentation\Field;
use ZF\Apigility\Documentation\Operation;
use ZF\Apigility\Documentation\Service as BaseService;
use ZF\Apigility\Documentation\Swagger\Model\ModelGenerator;

class Service extends BaseService
{
    const DEFAULT_TYPE = 'string';
    const ARRAY_TYPE = 'array';

    /**
     * @var BaseService
     */
    protected $service;

    /**
     * @var ModelGenerator
     */
    private $modelGenerator;

    /**
     * @param BaseService $service
     */
    public function __construct(BaseService $service)
    {
        $this->service = $service;
        $this->modelGenerator = new ModelGenerator();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->cleanEmptyValues([
            'tags' => $this->getTags(),
            'paths' => $this->cleanEmptyValues($this->getPaths()),
            'definitions' => $this->getDefinitions()
        ]);
    }

    /**
     * @return array
     */
    private function getTags()
    {
        return [
            $this->cleanEmptyValues([
                'name' => $this->service->getName(),
                'description' => $this->service->getDescription(),
            ])
        ];
    }

    /**
     * @return array
     */
    private function getPaths()
    {
        $route = $this->getRouteWithReplacements();
        if ($this->isRestService()) {
            return $this->getRestPaths($route);
        }
        return $this->getOtherPaths($route);
    }

    /**
     * @return string
     */
    private function getRouteWithReplacements()
    {
        // routes and parameter mangling ([:foo] will become {foo}
        $search = ['[', ']', '{/', '{:'];
        $replace = ['{', '}', '/{', '{'];
        return str_replace($search, $replace, $this->service->route);
    }

    /**
     * @return bool
     */
    private function isRestService()
    {
        return ($this->service->routeIdentifierName);
    }

    /**
     * @param string $route
     * @return array
     */
    private function getRestPaths($route)
    {
        $entityOperations = $this->getEntityOperationsData($route);
        $collectionOperations = $this->getCollectionOperationsData($route);
        $collectionPath = str_replace('/{' . $this->service->routeIdentifierName . '}', '', $route);
        if ($collectionPath === $route) {
            return [
                $collectionPath => array_merge($collectionOperations, $entityOperations)
            ];
        }
        return [
            $collectionPath => $collectionOperations,
            $route => $entityOperations
        ];
    }

    /**
     * @param string $route
     * @return array
     */
    private function getOtherPaths($route)
    {
        $operations = $this->getOtherOperationsData($route);
        return [$route => $operations];
    }

    /**
     * @param string $route
     * @return array
     */
    private function getEntityOperationsData($route)
    {
        $urlParameters = $this->getURLParametersRequired($route);
        $operations = $this->service->getEntityOperations();
        return $this->getOperationsData($operations, $urlParameters);
    }

    /**
     * @param string $route
     * @return array
     */
    private function getCollectionOperationsData($route)
    {
        $urlParameters = $this->getURLParametersNotRequired($route);
        unset($urlParameters[$this->service->routeIdentifierName]);
        $operations = $this->service->operations;
        return $this->getOperationsData($operations, $urlParameters);
    }

    /**
     * @param string $route
     * @return array
     */
    private function getOtherOperationsData($route)
    {
        $urlParameters = $this->getURLParametersRequired($route);
        $operations = $this->service->operations;
        return $this->getOperationsData($operations, $urlParameters);
    }

    /**
     * @param string $route
     * @param array $urlParameters
     * @return array
     */
    private function getOperationsData($operations, $urlParameters)
    {
        $operationsData = [];
        foreach ($operations as $operation) {
            $method = $this->getMethodFromOperation($operation);
            $parameters = array_values($urlParameters);
            if ($this->isMethodPostPutOrPatch($method)) {
                $parameters[] = $this->getPostPatchPutBodyParameter();
            }
            $pathOperation = $this->getPathOperation($operation, $parameters);
            $operationsData[$method] = $pathOperation;
        }
        return $operationsData;
    }

    /**
     * @param string $route
     * @return array
     */
    private function getURLParametersRequired($route)
    {
        return $this->getURLParameters($route, true);
    }

    /**
     * @param string $route
     * @return array
     */
    private function getURLParametersNotRequired($route)
    {
        return $this->getURLParameters($route, false);
    }

    /**
     * @param string $route
     * @param bool $required
     * @return array
     */
    private function getURLParameters($route, $required)
    {
        // find all parameters in Swagger naming format
        preg_match_all('#{([\w\d_-]+)}#', $route, $parameterMatches);

        $templateParameters = [];
        foreach ($parameterMatches[1] as $paramSegmentName) {
            $templateParameters[$paramSegmentName] = [
                'in' => 'path',
                'name' => $paramSegmentName,
                'description' => 'URL parameter ' . $paramSegmentName,
                'type' => 'string',
                'required' => $required,
                'minimum' => 0,
                'maximum' => 1
            ];
        }
        return $templateParameters;
    }

    /**
     * @return array
     */
    private function getPostPatchPutBodyParameter()
    {
        return [
            'in' => 'body',
            'name' => 'body',
            'required' => true,
            'schema' => [
                '$ref' => '#/definitions/' . $this->service->getName()
            ]
        ];
    }

    /**
     * @param string $method
     * @return bool
     */
    private function isMethodPostPutOrPatch($method)
    {
        return in_array(strtolower($method), ['post', 'put', 'patch']);
    }

    /**
     * @param Operation $operation
     * @return string
     */
    private function getMethodFromOperation(Operation $operation)
    {
        return strtolower($operation->getHttpMethod());
    }

    /**
     * @param Operation $operation
     * @param array $parameters
     * @return array
     */
    private function getPathOperation(Operation $operation, $parameters)
    {
        return $this->cleanEmptyValues([
            'tags' => [$this->service->getName()],
            'description' => $operation->getDescription(),
            'parameters' => $parameters,
            'produces' => $this->service->getRequestAcceptTypes(),
            'responses' => $this->getResponsesFromOperation($operation),
        ]);
    }

    /**
     * @param Operation $operation
     * @return array
     */
    private function getResponsesFromOperation(Operation $operation)
    {
        $responses = [];
        $responseStatusCodes = $operation->getResponseStatusCodes();
        foreach ($responseStatusCodes as $responseStatusCode) {
            $code = intval($responseStatusCode['code']);
            $responses[$code] = $this->cleanEmptyValues([
                'description' => $responseStatusCode['message'],
                'schema' => $this->getResponseSchema($operation, $code),
            ]);
        }
        return $responses;
    }

    /**
     * @param Operation $operation
     * @param int $code
     * @return null|array If the return code is neither 200 or 201, returns null.
     *     Otherwise, it retrieves the response description, passes it to the
     *     model generator, and uses the returned value.
     */
    private function getResponseSchema(Operation $operation, $code)
    {
        if ($code === 200 || $code === 201) {
            return $this->modelGenerator->generate($operation->getResponseDescription());
        }
    }

    /**
     * @return array
     */
    private function getDefinitions()
    {
        if (! $this->serviceContainsPostPutOrPatchMethod()) {
            return [];
        }
        $modelFromFields = $this->getModelFromFields();
        $modelFromPostDescription = $this->getModelFromFirstPostDescription();
        $model = array_replace_recursive($modelFromFields, $modelFromPostDescription);
        return [$this->service->getName() => $model];
    }

    /**
     * @return bool
     */
    private function serviceContainsPostPutOrPatchMethod()
    {
        foreach ($this->getAllOperations() as $operation) {
            $method = $this->getMethodFromOperation($operation);
            if ($this->isMethodPostPutOrPatch($method)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    private function getModelFromFields()
    {
        $required = $properties = [];

        foreach ($this->getFieldsForDefinitions() as $field) {
            if (! $field instanceof Field) {
                continue;
            }

            $properties[$field->getName()] = $this->getFieldProperties($field);
            if ($field->isRequired()) {
                $required[] = $field->getName();
            }
        }

        return $this->cleanEmptyValues([
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
        ]);
    }

    /**
     * @return array
     */
    private function getModelFromFirstPostDescription()
    {
        $firstPostDescription = $this->getFirstPostRequestDescription();
        if (! $firstPostDescription) {
            return [];
        }
        return $this->modelGenerator->generate($firstPostDescription) ?: [];
    }

    /**
     * @return null|mixed Returns null if no POST operations are discovered;
     *     otherwise, returns the request description from the first POST
     *     operation discovered.
     */
    private function getFirstPostRequestDescription()
    {
        foreach ($this->getAllOperations() as $operation) {
            $method = $this->getMethodFromOperation($operation);
            if ($method === 'post') {
                return $operation->getRequestDescription();
            }
        }
        return null;
    }

    /**
     * @return null|array
     */
    private function getFieldsForDefinitions()
    {
        // Fields are part of the default input filter when present.
        $fields = $this->service->fields;
        if (isset($fields['input_filter'])) {
            $fields = $fields['input_filter'];
        }
        return $fields;
    }

    /**
     * @param Field $field
     * @return array
     */
    private function getFieldProperties(Field $field)
    {
        $type = $this->getFieldType($field);
        $properties = [];
        $properties['type'] = $type;
        if ($type === self::ARRAY_TYPE) {
            $properties['items'] = ['type' => self::DEFAULT_TYPE];
        }
        $properties['description'] = $field->getDescription();
        return $this->cleanEmptyValues($properties);
    }

    /**
     * @param Field $field
     * @return string
     */
    private function getFieldType(Field $field)
    {
        return method_exists($field, 'getFieldType') && ! empty($field->getFieldType())
            ? $field->getFieldType()
            : self::DEFAULT_TYPE;
    }

    /**
     * @return array
     */
    private function getAllOperations()
    {
        $entityOperations = $this->service->getEntityOperations();
        if (is_array($entityOperations)) {
            return array_merge($this->service->getOperations(), $this->service->getEntityOperations());
        }
        return $this->service->getOperations();
    }

    /**
     * @param array $data
     * @return array $data omitting empty values
     */
    private function cleanEmptyValues(array $data)
    {
        return array_filter($data, function ($item) {
            return ! empty($item);
        });
    }
}
