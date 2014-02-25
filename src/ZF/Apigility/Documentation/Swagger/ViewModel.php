<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger;

use ZF\ContentNegotiation\JsonModel;

class ViewModel extends JsonModel
{
    /**
     * @return array|\Traversable
     */
    public function getVariables()
    {
        if (!array_key_exists('type', $this->variables)
            || empty($this->variables['type'])
        ) {
            return $this->variables;
        }

        switch ($this->variables['type']) {
            case 'api-list':
                return $this->variables['documentation'];
            case 'api':
                $model = new Api($this->variables['documentation']);
                return $model->toArray();
            case 'service':
                $model = new Service($this->variables['documentation'], $this->variables['baseUrl']);
                return $model->toArray();
        }
    }
}
