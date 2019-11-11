<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $sm = $e->getApplication()->getServiceManager();
        $router = $sm->get('router');
        $request = $sm->get('request');
        $matchedRoute = $router->match($request);
        $params = $matchedRoute->getParams();
        
        $cookieData = $request->getCookie('someCookie', 'default'); //print_r($cookieData);
        
        
        $lang = @$params['lang'];
        if(empty($lang))
        {
            if(@$cookieData['ck_lang'])
            {
                $lang = $cookieData['ck_lang'];
            }
            else
            {
                $lang = 'fr';
            }
        }
	    
        if(isset($lang) && $lang !== '') 
        {
            $translator = $e->getApplication()->getServiceManager()->get('MvcTranslator');
            if($lang == 'fr')
            {
                $translator->setLocale('fr_FR');
            }
            else
            {
                $translator->setLocale('en_US');
            }
        }
        $viewModel = $e->getApplication()->getMvcEvent()->getViewModel();
        $viewModel->action = $params['action'];
        $viewModel->id = $params['id'];
        $viewModel->controller = $params['controller'];
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
