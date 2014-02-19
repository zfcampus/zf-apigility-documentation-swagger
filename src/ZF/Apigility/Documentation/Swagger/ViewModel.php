<?php

namespace ZF\Apigility\Documentation\Swagger;

use ZF\ContentNegotiation\JsonModel;

class ViewModel extends JsonModel
{
    public function getVariables()
    {
        /** @var \ArrayObject $dvars */
        $dvars = parent::getVariables();
$vs = $dvars->getArrayCopy();


        if (isset($dvars['version'])) {
            // model is API
            $svars = array(
                'apiVersion' => $dvars['version'],
                'swaggerVersion' => '1.2',
                'apis' => array()
            );
            foreach ($dvars['services'] as $service) {
                $svars['apis'][] = array(
                    'description' => 'Operations about ' . $service['name'],
                    'path' => '/' . $service['name']
                );
            }
        } else {
            // model is Service
            $svars = array(
                'apiVersion' => $dvars['version'],
                'swaggerVersion' => '1.2',
                'resourcePath' => '/fill-me-in',
                'apis' => array(
                    array(
                        'operations' => array(),
                        'path' => $dvars['route']
                    )
                ),
                'produces' => $dvars['response_content_types'],
                'models' => array(
                    $dvars['name'] => array(
                        'id' => $dvars['name'],
                        'required' => array(),
                        'properties' => array()
                    )
                )
            );
            $i = 0;
            foreach ($dvars['operations'] as $method => $operation) {
                $svars['apis'][0]['operations'][$i] = array(
                    'method' => $method,
                    'notes' => $operation['description'],
                    'nickname' => $method . ' for ' . $dvars['name'],
                    'type' => $dvars['name']
                );
                $i++;
            }
            foreach ($dvars['fields'] as $fieldName => $field) {
                $svars['models'][$dvars['name']]['properties'][$fieldName] = array(
                    'type' => 'string',
                    'description' => $field['description']
                );
                if ($field['required'] == true) {
                    $svars['models'][$dvars['name']]['required'][] = $fieldName;
                }
            }
        }
        return $svars;
    }
}
 