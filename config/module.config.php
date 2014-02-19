<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

return array(
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
