<?php

/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger;

use ZF\Apigility\Documentation\Api as BaseApi;

class Api extends BaseApi
{

    /**
     * @var BaseApi
     */
    protected $api;

    /**
     * @param BaseApi $api
     */
    public function __construct(BaseApi $api)
    {
        $this->api = $api;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $output = [
            'swagger' => '2.0',
            'info' => [
                'title' => $this->api->getName(),
                'version' => (string)$this->api->getVersion()
            ]
        ];
        foreach ($this->api->services as $service) {
            $outputService = new Service($service);
            $output = array_merge_recursive($output, $outputService->toArray());
        }
        return $output;
    }
}
