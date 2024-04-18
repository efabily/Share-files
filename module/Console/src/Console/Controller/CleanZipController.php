<?php

namespace Console\Controller;

use Zend, App;
use Zend\Console\Console;
use Zend\Mvc\Controller\AbstractActionController, Zend\Console\Request as ConsoleRequest;


class CleanZipController extends \Console\BaseController
{


    function indexAction()
    {
        $this->_checIskLocked(__METHOD__);
        $this->_lock(__METHOD__);

        try
        {
            $path = PUBLIC_DIRECTORY . "/uploads";
            $now   = time();
            $flag = false;

            foreach(glob("$path/*.zip") as $filename)
            {
                $pathinfo = pathinfo($filename);

                if(($now - filemtime($filename)) >= (60 * 60 * 24) * 3)
                {
                    $flag = true;
                    $msg = "removeing file {$pathinfo['basename']}";
                    $this->_writeMessage($msg);

                    unlink($filename);
                }
            }

            # /usr/local/lib/php index.php clean-zip

            if(!$flag)
            {
                $msg = "0 fles removed";
                $this->_writeMessage($msg);
            }
        }
        catch(\Exception $e)
        {
            $msg = $e->getMessage();
            $this->_writeError($msg);
        }

        $this->_unlock(__METHOD__);
    }
}