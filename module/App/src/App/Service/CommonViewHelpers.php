<?php

namespace App\Service;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class CommonViewHelpers implements AbstractFactoryInterface
{


    public function canCreateServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {
        $requestedName = str_replace('_', '\\', $requestedName);
        $className = "App\View\Helper\\$requestedName";
        if(class_exists($className))
        {
            return true;
        }
        
        return false;
    }


    public function createServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {
        $requestedName = str_replace('_', '\\', $requestedName);
        $className = "App\View\Helper\\$requestedName";
        return new $className();
    }
}