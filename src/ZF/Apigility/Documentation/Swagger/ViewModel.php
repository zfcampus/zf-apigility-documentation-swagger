<?php

namespace ZF\Apigility\Documentation\Swagger;

use ZF\ContentNegotiation\JsonModel;

class ViewModel extends JsonModel
{
    public function getVariables()
    {
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
 