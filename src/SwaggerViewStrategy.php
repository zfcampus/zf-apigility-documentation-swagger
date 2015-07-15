<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Renderer\JsonRenderer;
use Zend\View\ViewEvent;

class SwaggerViewStrategy extends AbstractListenerAggregate
{
    /**
     * @var ViewModel
     */
    protected $model;

    /**
     * @var JsonRenderer
     */
    protected $renderer;

    /**
     * @param JsonRenderer $renderer
     */
    public function __construct(JsonRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 200)
    {
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, [$this, 'selectRenderer'], $priority);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, [$this, 'injectResponse'], $priority);
    }

    /**
     * @param ViewEvent $e
     * @return null|JsonRenderer
     */
    public function selectRenderer(ViewEvent $e)
    {
        $model = $e->getModel();
        if (! $model instanceof ViewModel) {
            return;
        }

        $this->model = $model;
        return $this->renderer;
    }

    /**
     * @param ViewEvent $e
     */
    public function injectResponse(ViewEvent $e)
    {
        if (! $this->model instanceof ViewModel) {
            return;
        }

        $response = $e->getResponse();
        if (! method_exists($response, 'getHeaders')) {
            return;
        }

        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/vnd.swagger+json');
    }
}
