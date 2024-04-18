<?php

namespace App;

return array (
	'service_manager' => array (
			'aliases' => array(
				'adapter' => 'Zend\Db\Adapter\Adapter'
			),
			'abstract_factories' => array (
					'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
					'Zend\Log\LoggerAbstractServiceFactory',
					'App\Service\CommonFactory'
			),
			'factories' => array (
				'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory' 
			),
			
			'initializers' => array (
					function ($instance,\Zend\ServiceManager\ServiceManager $sm) {
						if ($instance instanceof \App\Model\AbstractModel) {
							$instance->setServiceLocator ( $sm );
							
							$adapter = $sm->get ( 'adapter' );
							$instance->setDbAdapter ( $adapter );
						}
						
						if ($instance instanceof \Zend\Db\Adapter\AdapterAwareInterface) {
							$instance->setServiceLocator ( $sm );
							$adapter = $sm->get ( 'adapter' );
							
							$instance->setDbAdapter ( $adapter );
						}
						
						if ($instance instanceof \App\Db\AbstractDb) {
							$adapterKey = $instance->getAdpaterKey ();
							
							if ($adapterKey) {
								$adapter = $sm->get ( $adapterKey );
							} else {
								$adapter = $sm->get ( 'adapter' );
							}
							
							$instance->setServiceLocator ( $sm );
							$instance->setAdapter ( $adapter );
							$instance->initialize ();
							
							$entityClassName = $instance->getEntityClassName ();
							if ($entityClassName) {
								$instance->getResultSetPrototype ()->setArrayObjectPrototype ( $sm->get ( $entityClassName ) );
							}
						}
					} 
			) 
	),
	'translator' => array (
			'locale' => 'en_US',
			'translation_file_patterns' => array (
					array (
							'type' => 'gettext',
							'base_dir' => __DIR__ . '/../language',
							'pattern' => '%s.mo' 
					) 
			) 
	),
	'controllers' => array (
			#'invokables' => array (
					#'Auth\Controller\Index' => 'Auth\Controller\IndexController',
					#'Files\Controller\Index' => 'Files\Controller\IndexController' 
			#),

			'abstract_factories' => array(
					'App\Service\ControllerFactory'
			)
	),
	'view_manager' => array (
			'display_not_found_reason' => true,
			'display_exceptions' => true,
			'doctype' => 'HTML5',
			'not_found_template' => 'error/404',
			'exception_template' => 'error/index',
			'template_map' => array (
					'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
					'layout/login' => __DIR__ . '/../view/layout/login.phtml',
					'layout/users' => __DIR__ . '/../view/layout/users.phtml',
					'layout/backend' => __DIR__ . '/../view/layout/backend.phtml',
					'layout/blank' => __DIR__ . '/../view/layout/blank.phtml',
					'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
					'error/404' => __DIR__ . '/../view/error/404.phtml',
					'error/index' => __DIR__ . '/../view/error/index.phtml' 
			),
			'template_path_stack' => array (
					__DIR__ . '/../view' 
			) 
	),

	'view_helpers' => array(
        'abstract_factories' => array(
            'App\Service\CommonViewHelpers' 
        ),

        'invokables' => array(
            'communicator' => 'App\View\Helper\Communicator',
            'HtmlSelect' => 'Com\View\Helper\HtmlSelect',
        )
	),

	'mail' => array(

        'transport' => array(

            'smtp1' => array(
                'options' => array(
                    'name' => 'localhost',
                    'host' => 'hs17.name.com',
                    'port' => 465,
                    'connection_class' => 'login',
                    'connection_config' => array(
                        'username' => 'info@inthezonemusic.com',
                        'password' => '1mnri4tTdbpJ',
                        'ssl' => 'ssl' 
                    ) 
                ) 
            ),
        ),

        'from' => array(
            'no-reply' => array(
                'name' => 'In The Zone Music',
                'email' => 'info@inthezonemusic.com' 
            ),
        )
    ),
);
