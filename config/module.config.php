<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

return array(
    'router' => array(
        'routes' => array(
            'zf-apigility-documentation-swagger' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/swagger',
                    'defaults' => array(
                        'controller' => 'ZF\Apigility\Documentation\Swagger\SwaggerUi',
                        'action'     => 'list',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'training-and-certification' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/:api',
                            'defaults' => array(
                                'action' => 'show',
                            ),
                        ),
                        'may_terminate' => true,
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'ZF\Apigility\Documentation\Swagger\SwaggerUi' => 'ZF\Apigility\Documentation\Swagger\SwaggerUiController',
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'zf-apigility-documentation-swagger' => __DIR__ . '/../view',
        ),
    ),

    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../asset',
            ),
        ),
    ),

    'zf-content-negotiation' => array(
        'accept_whitelist' => array(
            'ZF\Apigility\Documentation\Controller' => array(
                0 => 'application/vnd.swagger+json',
            ),
        ),
        'selectors' => array(
            'Documentation' => array(
                'ZF\Apigility\Documentation\Swagger\ViewModel' => array(
                    'application/vnd.swagger+json',
                ),
            )
        )
    ),
);
