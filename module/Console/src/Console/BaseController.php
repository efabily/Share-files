<?php

namespace Console;

use Zend, App;
use Zend\Console\Console;
use Zend\Mvc\Controller\AbstractActionController, Zend\Console\Request as ConsoleRequest;


class BaseController extends \App\Controller\AbstractController
{


	// ####################################
    // Common methods
    // ####################################
    protected function _checIskLocked($methodName)
    {
        if($this->_isLocked($methodName))
        {
            $msg = 'Already running...';
            
            $console = Console::getInstance();
            $console->writeLine($msg, 2);
            exit();
        }
    }


    protected function _checkRequest()
    {
        $request = $this->getRequest();
        if(! ($request instanceof Zend\Console\Request))
        {
            throw new RuntimeException('You can only use this action from a console!');
        }
    }


    protected function _isLocked($methodName)
    {
        $methodName = str_replace('\\', '.', $methodName);
        $methodName = str_replace(':', '-', $methodName);
        
        $fileName = "data/$methodName.lock";
        return file_exists($fileName);
    }


    protected function _lock($methodName)
    {
        $methodName = str_replace('\\', '.', $methodName);
        $methodName = str_replace(':', '-', $methodName);
        
        $fileName = "data/$methodName.lock";
        $handler = fopen($fileName, 'w') or die("can't open file");
        fclose($handler);
    }


    protected function _unlock($methodName)
    {
        $methodName = str_replace('\\', '.', $methodName);
        $methodName = str_replace(':', '-', $methodName);
        
        $fileName = "data/$methodName.lock";
        unlink($fileName);
    }


    protected function _writeError($msg)
    {
        $console = Console::getInstance();
        
        $console->writeLine('---------------------------', 2);
        $console->writeLine("ERROR", 10);
        $console->writeLine($msg, 2);
        $console->writeLine('---------------------------', 2);
    }


    protected function _writeMessage($msg)
    {
        $console = Console::getInstance();
        $console->writeLine($msg, 5);
    }
}