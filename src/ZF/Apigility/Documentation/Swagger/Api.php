<?php

namespace ZF\Apigility\Documentation\Swagger;

use ZF\Apigility\Documentation\Api as BaseApi;

class Api extends BaseApi
{
    protected $api;

    public function __construct(BaseApi $api)
    {
        $this->api = $api;
    }

    public function toArray()
    {
        $services = array();
        foreach ($this->api->services as $service) {
            $services[] = array(
                'description' => 'Operations about ' . $service->getName(),
                'path' => '/' . $service->getName()
            );
        }

        return array(
            'apiVersion' => $this->api->version,
            'swaggerVersion' => '1.2',
            'apis' => $services
        );
    }
}
 