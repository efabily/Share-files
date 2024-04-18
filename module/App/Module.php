<?php

namespace App;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\Event;

class Module {

	/**
     *
     * @var \Zend\Mvc\MvcEvent
     */
    static $MVC_EVENT;

	
	/**
	 * Configure PHP ini settings on the bootstrap event
	 * 
	 * @param Event $e        	
	 */
	public function onBootstrap(Event $e) {
		
		self::$MVC_EVENT = $e;

		$this->_phpSettings($e);
		$this->_setupAuthorisation($e);
		$this->_setupGlobalVariables($e);
	}

	protected function _phpSettings(MvcEvent $e)
	{
		$app = $e->getParam ('application');
		$config = $app ->getConfig();
		$phpSettings = isset($config['phpSettings']) ? $config['phpSettings'] : false;
		if ($phpSettings) {
			foreach ( $phpSettings as $key => $value )
			{
				ini_set ( $key, $value );
			}
		}
		
		if(isset($config['error_reporting']))
		{
			error_reporting($config['error_reporting']);
		}
		else
		{
			error_reporting(0);
		}
	} 
	
	
	function _setupAuthorisation(MvcEvent $mvcEvent)
	{
		$eventManager = $mvcEvent->getApplication()->getEventManager();

		// set higher priority to this event
		$eventManager->attach('dispatch', function (MvcEvent $event)
		{

			$goTo = function($routeName, array $params = array()) use($event) {

				$response = $event->getResponse();
				$options['name'] = $routeName;

				$url = $event->getRouter()
					->assemble($params, $options);

				$headers = $response->getHeaders()
					->addHeaderLine('Location', $url);

				$response->setStatusCode(302);
				$response->sendHeaders();
			};

			#
			$serviceManager = $event->getApplication()
				->getServiceManager();

			$response = $event->getResponse();

			#
            $userService = $serviceManager->get("zfcuser_user_service");
			$authService = $userService->getAuthService();

			#
			$controller = $event->getRouteMatch()
                ->getParam('controller');

            $controllerClass = $controller . 'Controller';

            $requireAuth = is_subclass_of($controllerClass, '\App\Controller\AuthenticatedController');
            $requireAuth = $requireAuth || is_subclass_of($controllerClass, '\App\Controller\BackendController');
            $requireAuth = $requireAuth || is_subclass_of($controllerClass, '\App\Controller\UserController');
           
            if($requireAuth)
            {
            	

				if($authService->hasIdentity())
				{
					$identity = $authService->getIdentity();
					if(!isset($identity->role))
					{
						$goTo('auth', array('action' => 'init'));
					}
					elseif(2 == $identity->role)
					{
						if(is_subclass_of($controllerClass, '\App\Controller\BackendController'))
						{
							echo '<strong>Unauthorized access</strong>';
							exit;
						}
					}
				}
				else
				{
					$goTo('zfcuser/logout');
				}
            }
            else
            {
            	if(('zfcuserController'  == $controllerClass) && ($authService->hasIdentity()))
            	{
            		$goTo('auth', array('action' => 'init'));
            	}
            }

		}, 1000);
	}


	protected function _setupGlobalVariables(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        
        $eventManager->attach('dispatch', function (MvcEvent $event)
        {
        	$serviceManager = $event->getApplication()
				->getServiceManager();

            $userService = $serviceManager->get("zfcuser_user_service");
			$authService = $userService->getAuthService();

            $hasIdentity = $authService->hasIdentity();
            
            $globals = array();
            
            if($hasIdentity)
            {
                $globals['has_identity'] = true;
                $globals['identity'] = $authService->getIdentity();
            }
            else
            {
                $globals['has_identity'] = false;
            }

            $controller = $event->getTarget();
            $controller->layout()->globals = $globals;
        });
    }
	
	
	
	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}
	public function getAutoloaderConfig() {
		return array (
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => array (
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__ 
						) 
				) 
		);
	}
}
