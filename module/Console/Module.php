<?php

namespace Console;

use Zend, Com;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface, Zend\ModuleManager\Feature\ConfigProviderInterface, Zend\ModuleManager\Feature\ConsoleUsageProviderInterface, Zend\Console\Adapter\AdapterInterface as Console;


class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ConsoleUsageProviderInterface
{


    function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }


    public function getConsoleUsage(Console $console)
    {
        return array(
            
            // Describe available commands
            'clean-zip' => 'This will remove all zip files from uploads folder older than 1 week',
        );
    }


    function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__ 
                ) 
            ) 
        );
    }
}