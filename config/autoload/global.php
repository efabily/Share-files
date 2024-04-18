<?php
return array (
		
		'service_manager' => array (
				'factories' => array (
						'Zend\Db\Adapter\Adapter' => function ($sm) {
							$adapter = new \Zend\Db\Adapter\Adapter ( array (
									'driver' => 'Mysqli',
									'hostname' => '',
									'database' => '',
									'username' => '',
									'password' => "",
									'charset' => 'UTF8',
									'options' => array (
											'buffer_results' => true 
									) 
							) );
							return $adapter;
						} 
				) 
		),
		
		'phpSettings' => array (
				'display_startup_errors' => false,
				'display_errors' => false,
				'max_execution_time' => 60,
				'date.timezone' => 'America/La_Paz' 
		),
		
		'error_reporting' => 0 
);
