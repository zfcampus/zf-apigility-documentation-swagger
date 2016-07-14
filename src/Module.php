<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Documentation\Swagger;

class Module
{
    /**
     * Retrieve module configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Listen to bootstrap event.
     *
     * Attaches the module's render listener to the application event
     * manager instance.
     *
     * @param \Zend\Mvc\MvcEvent $e
     * @return void
     */
    public function onBootstrap($e)
    {
        $app    = $e->getApplication();
        $events = $app->getEventManager();
        $events->attach('render', [$this, 'onRender'], 100);
    }

    /**
     * Listen to render event.
     *
     * Attaches the SwaggerViewStrategy to the view event manager instance if
     * a Swagger view model is detected.
     *
     * @param \Zend\Mvc\MvcEvent $e
     * @return void
     */
    public function onRender($e)
    {
        $model = $e->getResult();
        if (! $model instanceof ViewModel) {
            return;
        }

        $app      = $e->getApplication();
        $services = $app->getServiceManager();
        $view     = $services->get('View');
        $events   = $view->getEventManager();
        $services->get(SwaggerViewStrategy::class)->attach($events);
    }
}
