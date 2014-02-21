<?php

namespace ZF\Apigility\Documentation\Swagger;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SwaggerUiController extends AbstractActionController
{
    public function listAction()
    {
        /** @var \ZF\Apigility\Documentation\ApiFactory $apiFactory */
        $apiFactory = $this->serviceLocator->get('ZF\Apigility\Documentation\ApiFactory');
        $apis = $apiFactory->createApiList();

        $viewModel = new ViewModel(array('apis' => $apis));
        $viewModel->setTemplate('zf-apigility-documentation-swagger/swagger-ui/list');
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function showAction()
    {
        $api = $this->params()->fromRoute('api');

        $viewModel = new ViewModel(array('api' => $api));
        $viewModel->setTemplate('zf-apigility-documentation-swagger/swagger-ui/show');
        $viewModel->setTerminal(true);
        return $viewModel;
    }
} 