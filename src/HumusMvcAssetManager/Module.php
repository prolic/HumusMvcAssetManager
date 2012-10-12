<?php

namespace HumusMvcAssetManager;

use Zend\Loader\StandardAutoloader;
use Zend\Loader\AutoloaderFactory;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use HumusMvc\MvcEvent;

/**
 * Module class
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    BootstrapListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/../../autoload_classmap.php',
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Callback method for dispatch events
     *
     * @param EventInterface $event
     */
    public function onDispatch(EventInterface $event)
    {
        $request        = $event->getRequest();
        $serviceManager = $event->getApplication()->getServiceManager();
        $assetManager   = $serviceManager->get(__NAMESPACE__ . '\Service\AssetManager');

        if (!$assetManager->resolvesToAsset($request)) {
            return;
        }

        $response = $event->getResponse();
        $response->setHttpResponseCode(200);

        return $assetManager->setAssetOnResponse($response);
    }

    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $event)
    {
        $eventManager = $event->getTarget()->getEventManager();
        $callback     = array($this, 'onDispatch');
        $priority     = 1000; // asset manager has higher priority
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, $callback, $priority);
    }
}
