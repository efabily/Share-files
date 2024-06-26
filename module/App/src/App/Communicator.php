<?php

namespace App;

use Zend;


class Communicator
{

    /**
     *
     * @var bool
     */
    protected $_success = true;

    /**
     *
     * @var bool
     */
    protected $_isWarning = false;

    /**
     *
     * @var string
     */
    protected $_successMessage = '';

    /**
     *
     * @var array
     */
    protected $_data = array();

    /**
     *
     * @var array
     */
    protected $_errors = array();


    /**
     *
     * @return bool
     *
     */
    function isSuccess()
    {
        return $this->_success;
    }


    /**
     *
     * @return bool
     *
     */
    function isWarning()
    {
        return $this->_isWarning;
    }


    /**
     *
     * @param string $message
     * @param bool $isWarning
     * @return \App\Communicator
     */
    function setSuccess($message = null, $isWarning = false)
    {
        $this->clearErrors();
        
        $this->_successMessage = $message;
        $this->_isWarning = (bool)$isWarning;
        $this->_success = true;
        return $this;
    }


    /**
     *
     * @return string
     */
    function getSuccessMessage()
    {
        return $this->_successMessage;
    }


    /**
     *
     * @return \App\Communicator
     */
    function setNoSuccess()
    {
        $this->_successMessage = '';
        $this->_isWarning = false;
        $this->_success = false;
        return $this;
    }


    /**
     *
     * @param string $message
     * @param string $key
     * @return \App\Communicator
     */
    function addError($message, $key = null)
    {
        $this->setNoSuccess();
        $message = (string)$message;
        
        if(! empty($key))
        {
            if(! isset($this->_errors[$key]))
            {
                $this->_errors[$key] = array();
            }
            
            $this->_errors[$key][] = $message;
        }
        else
        {
            array_push($this->_errors, $message);
        }
        
        return $this;
    }


    /**
     * Get all the error messages
     *
     * @example array(
     * 0 => 'Message 1',
     * 1 => 'Message 2',
     * 'key_1' => array(
     * 'messaje #1', 'message #2'
     * ),
     * 'key_2' => array(
     * 'messaje #3', 'message #4'
     * ),
     * )
     * @return array
     */
    function getErrors()
    {
        return $this->_errors;
    }


    /**
     *
     * @return array
     */
    function getGlobalErrors()
    {
        $r = array();
        
        $errors = $this->getErrors();
        foreach($errors as $key => $item)
        {
            if(is_numeric($key) && ! is_array($item))
            {
                $r[] = $item;
            }
        }
        
        return $r;
    }


    function getFieldErrors($fieldName = null)
    {
        $r = array();
        $errors = $this->getErrors();
        
        if(! empty($fieldName))
        {
            if(isset($errors[$fieldName]))
            {
                $r[] = $errors[$fieldName];
            }
        }
        else
        {
            foreach($errors as $key => $item)
            {
                if(is_array($item))
                {
                    $r[$key] = $item;
                }
            }
        }
        
        return $r;
    }


    /**
     *
     * @return \App\Communicator
     */
    function clearErrors()
    {
        $this->_errors = array();
        return $this;
    }


    /**
     *
     * @return array
     *
     */
    function getData()
    {
        return $this->_data;
    }


    /**
     *
     * @return \App\Communicator
     */
    function clearData()
    {
        $this->_data = array();
        return $this;
    }


    /**
     *
     * @param array $data
     * @return \App\Communicator
     */
    function setData(array $data)
    {
        $this->_data = $data;
        return $this;
    }


    /**
     *
     * @param array $data
     * @return \App\Communicator
     */
    function mergeData(array $data)
    {
        $data = array_merge($this->getData(), $data);
        $this->setData($data);
        return $this;
    }


    /**
     *
     * @param string $key
     * @param mixed $value
     * @return \App\Communicator
     */
    function addData($value, $key = null)
    {
        $data = array();
        if(! empty($key))
            $data[$key] = $value;
        else
            $data = $value;
        
        $this->mergeData($data);
        
        return $this;
    }


    /**
     *
     * @return string
     */
    function toJson()
    {
        return Zend\Json\Encoder::encode($this->toArray());
    }


    /**
     *
     * @return array
     */
    function toArray()
    {
        return array(
            'success' => $this->isSuccess(),
            'warning' => $this->isWarning(),
            'message' => $this->getSuccessMessage(),
            'data' => $this->getData(),
            'errors' => $this->getErrors() 
        );
    }


    function debug()
    {
        echo '<pre>';
        echo print_r($this->toArray(), 1);
        echo '</pre>';
    }
}