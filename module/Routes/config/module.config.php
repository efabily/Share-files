<?php

namespace Routes;

return array (
		'router' => array (
				'routes' => array (
						
						'home' => array (
								'type' => 'Zend\Mvc\Router\Http\Literal',
								'options' => array (
										'route' => '/',
										'defaults' => array (
												'controller' => 'Files\Controller\Index',
												'action' => 'list' 
										) 
								) 
						),
						
						'files' => array (
								'type' => 'Zend\Mvc\Router\Http\Segment',
								'options' => array (
										'route' => '/files/:action',
										'constraints' => array (),
										'defaults' => array (
												'controller' => 'Files\Controller\Index' 
										) 
								),
								
								'may_terminate' => true,
								
								'child_routes' => array (
										'wildcard' => array (
												'type' => 'Wildcard' 
										) 
								) 
						),
						
						
						'auth' => array (
								'type' => 'Zend\Mvc\Router\Http\Segment',
								'options' => array (
										'route' => '/auth/:action',
										'constraints' => array (),
										'defaults' => array (
												'controller' => 'Auth\Controller\Index'
										)
								),
						
								'may_terminate' => true,
						
								'child_routes' => array (
										'wildcard' => array (
												'type' => 'Wildcard'
										)
								)
						),
						
						'users' => array (
								'type' => 'Zend\Mvc\Router\Http\Segment',
								'options' => array (
										'route' => '/users/:action',
										'constraints' => array (),
										'defaults' => array (
												'controller' => 'User\Controller\Index'
										)
								),
						
								'may_terminate' => true,
						
								'child_routes' => array (
										'wildcard' => array (
												'type' => 'Wildcard'
										)
								)
						),
						
 						'change-password' => array (
 								'type' => 'Zend\Mvc\Router\Http\Literal',
 								'options' => array (
 										'route' => '/users/change-password',
 										'defaults' => array (
 												'controller' => 'User\Controller\Users',
 												'action' => 'change-password' 
 										) 
 								) 
 						),

				) 
		) 
);
