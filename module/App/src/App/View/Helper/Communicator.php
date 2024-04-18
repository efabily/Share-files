<?php

namespace App\View\Helper;

use Zend\View\Helper\AbstractHelper;


class Communicator extends AbstractHelper
{

    /**
     *
     * @var \App\Communicator
     */
    protected $communicator;


    /**
     *
     * @param \App\Communicator | null $reposnse
     */
    public function __invoke($communicator = null)
    {
        if($communicator instanceof \App\Communicator)
        {
            $this->communicator = $communicator;
        }
        else
        {
            $this->communicator = $this->view->communicator;
        }
        
        if(empty($this->communicator) || ! ($this->communicator instanceof \App\Communicator))
        {
            $this->communicator = new \App\Communicator();
        }
        
        return $this;
    }


    /**
     *
     * @return \App\View\Helper\Communicator
     */
    function printMessage()
    {
        $com = $this->getCommunicator();
        if($com->isSuccess())
        {
            $this->printSuccess();
        }
        else
        {
            $this->printErrors();
        }
        
        return $this;
    }


    /**
     *
     * @param string $fieldName
     * @return \App\View\Helper\Communicator
     */
    function printErrorClass($fieldName)
    {
        $com = $this->getCommunicator();
        $errors = $com->getFieldErrors($fieldName);
        
        if(count($errors))
        {
            echo ' field-error has-error';
        }
    }


    /**
     *
     * @param string $fieldName
     * @return \App\View\Helper\Communicator
     */
    function printFieldErrors($fieldName)
    {
        $com = $this->getCommunicator();
        $errors = $com->getFieldErrors($fieldName);
        
        if(count($errors))
        {
            echo '<div class="field-errors">';
            echo '<ul>';
            
            foreach($errors as $messages)
            {
                foreach($messages as $message)
                    echo "<li>$message</li>";
            }
            
            echo '</ul>';
            echo '</div>';
        }
        
        return $this;
    }


    /**
     *
     * @return \App\View\Helper\Communicator
     */
    function printErrors()
    {
        $com = $this->getCommunicator();
        $errors = $com->getGlobalErrors();
        
        if(count($errors))
        {
            echo '<div class="alert alert-danger user-errors">';
            echo '<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>';
            echo '<ul>';
            
            foreach($errors as $message)
            {
                echo "<li>$message</li>";
            }
            
            echo '</ul>';
            echo '</div>';
        }
        
        return $this;
    }


    /**
     *
     * @return \App\View\Helper\Communicator
     */
    function printSuccess()
    {
        $com = $this->getCommunicator();
        $message = $com->getSuccessMessage();
        
        if(! empty($message))
        {
            $isWarning = $com->isWarning();
            
            $cssClass = $isWarning ? 'alert-warning' : 'alert-success';
            
            echo sprintf('<div class="alert %s user-success">', $cssClass);
            echo '<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>';
            echo $message;
            echo '</div>';
        }
        
        return $this;
    }


    /**
     *
     * @return \App\Communicator
     */
    function getCommunicator()
    {
        return $this->communicator;
    }


    /**
     *
     * @return boolean
     */
    function isSuccess()
    {
        return $this->communicator->isSuccess();
    }
}
