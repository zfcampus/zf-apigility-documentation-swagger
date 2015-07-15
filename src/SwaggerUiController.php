<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZF\Apigility\Documentation\ApiFactory;

class SwaggerUiController extends AbstractActionController
{
    /**
     * @var ApiFactory
     */
    protected $apiFactory;

    /**
     * @param ApiFactory $apiFactory
     */
    public function __construct(ApiFactory $apiFactory)
    {
        $this->apiFactory = $apiFactory;
    }

    /**
     * List available APIs
     *
     * @return ViewModel
     */
    public function listAction()
    {
        $apis = $this->apiFactory->createApiList();

        $viewModel = new ViewModel(['apis' => $apis]);
        $viewModel->setTemplate('zf-apigility-documentation-swagger/list');
        return $viewModel;
    }

    /**
     * Show the Swagger UI for a given API
     *
     * @return ViewModel
     */
    public function showAction()
    {
        $api = $this->params()->fromRoute('api');

        $viewModel = new ViewModel(['api' => $api]);
        $viewModel->setTemplate('zf-apigility-documentation-swagger/show');
        $viewModel->setTerminal(true);
        return $viewModel;
    }
}
