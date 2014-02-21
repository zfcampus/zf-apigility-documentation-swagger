<?php

namespace ZF\Apigility\Documentation\Swagger;

use ZF\Apigility\Documentation\Service as BaseService;

class Service extends BaseService
{
    protected $service;

    public function __construct(BaseService $service)
    {
        $this->service = $service;
    }

    public function toArray()
    {
        // localize service object for brevity
        $service = $this->service;

        // find all operations
        $operations = array();
        foreach ($service->operations as $operation) {
            $method = $operation->getHttpMethod();
            $operations[] = array(
                'method' => $method,
                'notes' => $operation->getDescription(),
                'nickname' => $method . ' for ' . $service->api->getName(),
                'type' => $service->api->getName()
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

        $routeBasePath = substr($service->route, 0, strpos($service->route, '['));

        return array(
            'apiVersion' => $service->api->getVersion(),
            'swaggerVersion' => '1.2',
//            'basePath' => '', //$routeBasePath,
//            'resourcePath' => $routeBasePath,
            'apis' => array(array(
                'operations' => $operations,
                'path' => $routeBasePath,
            )),
            'produces' => $service->requestAcceptTypes,
            'models' => array(
                $service->api->getName() => array(
                    'id' => $service->api->getName(),
                    'required' => $requiredProperties,
                    'properties' => $properties
                )

            )
        );
    }
}
 