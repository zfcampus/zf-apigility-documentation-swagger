<?php

/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger;

use ZF\Apigility\Documentation\Service as BaseService;
use ZF\Apigility\Documentation\Operation;
use ZF\Apigility\Documentation\Field;
use ZF\Apigility\Documentation\Swagger\Model\ModelGenerator;

class Service extends BaseService
{

    const DEFAULT_TYPE = 'string';
    const ARRAY_TYPE = 'array';

    /**
     * @var BaseService
     */
    protected $service;
    protected $modelGenerator;

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

    protected function getTags()
    {
        return [
            $this->cleanEmptyValues([
                'name' => $this->service->getName(),
                'description' => $this->service->getDescription()
            ])
        ];
    }

    protected function getPaths()
    {
        $route = $this->getRouteWithReplacements();
        if ($this->isRestService()) {
            return $this->getRestPaths($route);
        }
        return $this->getOtherPaths($route);
    }

    protected function getRouteWithReplacements()
    {
        // routes and parameter mangling ([:foo] will become {foo}
        $search = ['[', ']', '{/', '{:'];
        $replace = ['{', '}', '/{', '{'];
        return str_replace($search, $replace, $this->service->route);
    }

    protected function isRestService()
    {
        return ($this->service->routeIdentifierName);
    }

    protected function getRestPaths($route)
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

    protected function getOtherPaths($route)
    {
        $operations = $this->getOtherOperationsData($route);
        return [$route => $operations];
    }

    protected function getEntityOperationsData($route)
    {
        $urlParameters = $this->getURLParametersRequired($route);
        $operations = $this->service->getEntityOperations();
        return $this->getOperationsData($operations, $urlParameters);
    }

    protected function getCollectionOperationsData($route)
    {
        $urlParameters = $this->getURLParametersNotRequired($route);
        unset($urlParameters[$this->service->routeIdentifierName]);
        $operations = $this->service->operations;
        return $this->getOperationsData($operations, $urlParameters);
    }

    protected function getOtherOperationsData($route)
    {
        $urlParameters = $this->getURLParametersRequired($route);
        $operations = $this->service->operations;
        return $this->getOperationsData($operations, $urlParameters);
    }

    protected function getOperationsData($operations, $urlParameters)
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

    protected function getURLParametersRequired($route)
    {
        return $this->getURLParameters($route, true);
    }

    protected function getURLParametersNotRequired($route)
    {
        return $this->getURLParameters($route, false);
    }

    protected function getURLParameters($route, $required)
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

    protected function getPostPatchPutBodyParameter()
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

    protected function isMethodPostPutOrPatch($method)
    {
        return in_array($method, ['post', 'put', 'patch']);
    }

    protected function getMethodFromOperation(Operation $operation)
    {
        return strtolower($operation->getHttpMethod());
    }

    protected function getPathOperation(Operation $operation, $parameters)
    {
        return $this->cleanEmptyValues([
                'tags' => [$this->service->getName()],
                'description' => $operation->getDescription(),
                'parameters' => $parameters,
                'produces' => $this->service->getRequestAcceptTypes(),
                'responses' => $this->getResponsesFromOperation($operation)
        ]);
    }

    protected function getResponsesFromOperation(Operation $operation)
    {
        $responses = [];
        $responseStatusCodes = $operation->getResponseStatusCodes();
        foreach ($responseStatusCodes as $responseStatusCode) {
            $code = intval($responseStatusCode['code']);
            $responses[$code] = $this->cleanEmptyValues([
                'description' => $responseStatusCode['message'],
                'schema' => $this->getResponseSchema($operation, $code)
            ]);
        }
        return $responses;
    }

    protected function getResponseSchema(Operation $operation, $code)
    {
        if ($code === 200 || $code === 201) {
            return $this->modelGenerator->generate($operation->getResponseDescription());
        }
    }

    protected function getDefinitions()
    {
        if (!$this->serviceContainsPostPutOrPatchMethod()) {
            return [];
        }
        $modelFromFields = $this->getModelFromFields();
        $modelFromPostDescription = $this->getModelFromFirstPostDescription();
        $model = array_replace_recursive($modelFromFields, $modelFromPostDescription);
        return [$this->service->getName() => $model];
    }

    protected function serviceContainsPostPutOrPatchMethod()
    {
        foreach ($this->getAllOperations() as $operation) {
            $method = $this->getMethodFromOperation($operation);
            if ($this->isMethodPostPutOrPatch($method)) {
                return true;
            }
        }
    }

    protected function getModelFromFields()
    {
        $required = $properties = [];
        $fields = $this->getFieldsForDefinitions();
        foreach ($fields as $field) {
            $properties[$field->getName()] = $this->getFieldProperties($field);
            if ($field->isRequired()) {
                $required[] = $field->getName();
            }
        }
        return $this->cleanEmptyValues([
                'type' => 'object',
                'properties' => $properties,
                'required' => $required
        ]);
    }

    protected function getModelFromFirstPostDescription()
    {
        $firstPostDescription = $this->getFirstPostRequestDescription();
        $model = $this->modelGenerator->generate($firstPostDescription);
        return $model ? $model : [];
    }

    protected function getFirstPostRequestDescription()
    {
        foreach ($this->getAllOperations() as $operation) {
            $method = $this->getMethodFromOperation($operation);
            if ($method === 'post') {
                return $operation->getRequestDescription();
            }
        }
    }

    protected function getFieldsForDefinitions()
    {
        // Fields are part of the default input filter when present.
        $fields = $this->service->fields;
        if (isset($fields['input_filter'])) {
            $fields = $fields['input_filter'];
        }
        return $fields;
    }

    protected function getFieldProperties(Field $field)
    {
        $type = $this->getFieldType($field);
        $properties =[];
        $properties['type']=$type;
        if ($type === self::ARRAY_TYPE) {
            $properties['items'] = ['type' => self::DEFAULT_TYPE];
        }
        $properties['description'] =$field->getDescription();
        return $this->cleanEmptyValues($properties);
    }

    protected function getFieldType(Field $field)
    {
        return (method_exists($field, 'getFieldType') &&
            !empty($field->getFieldType())) ?
            $field->getFieldType() : self::DEFAULT_TYPE;
    }

    protected function getAllOperations()
    {
        $entityOperations = $this->service->getEntityOperations();
        if (is_array($entityOperations)) {
            return array_merge($this->service->getOperations(), $this->service->getEntityOperations());
        }
        return $this->service->getOperations();
    }

    protected function cleanEmptyValues(array $data)
    {
        return array_filter($data, function ($item) {
            return !empty($item);
        });
    }
}
