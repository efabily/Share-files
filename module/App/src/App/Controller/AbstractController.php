<?php

namespace App\Controller;

use App;
use Zend;

abstract class AbstractController extends Zend\Mvc\Controller\AbstractActionController
{

    /**
     *
     * @var message
     */
    protected $viewVars = array();


    /**
     *
     * @param mixed $key
     * @param string $Value
     * @return \App\Controller\AbstractController
     */
    function assign($key, $value = null)
    {
        if(is_object($key))
        {
            if(method_exists($key, 'toArray'))
            {
                $key = $key->toArray();
            }
            elseif(method_exists($key, 'getArrayCopy'))
            {
                $key = $key->getArrayCopy();
            }
        }
        
        if(is_array($key))
        {
            foreach($key as $a => $b)
            {
                $this->viewVars[$a] = $b;
            }
        }
        else
        {
            $this->viewVars[$key] = $value;
        }
        
        return $this;
    }


    /**
     *
     * @param \App\Communicator $communicator
     * @return \App\Controller\AbstractController
     */
    function saveCommunicator(\App\Communicator $communicator)
    {
        $session = new \Zend\Session\Container();
        $session->communicator = $communicator;
        
        return $this;
    }



    /**
     *
     * @return \App\Controller\AbstractController
     */
    function loadCommunicator()
    {
        $session = new \Zend\Session\Container();
        
        if(isset($session->communicator))
        {
            $this->setCommunicator($session->communicator);
            $session->offsetUnset('communicator');
        }
        
        return $this;
    }


    /**
     *
     * @param \App\Communicator $communicator
     * @return \App\Controller\AbstractController
     */
    function setCommunicator(\App\Communicator $communicator)
    {
        $this->viewVars['communicator'] = $communicator;
        
        return $this;
    }


    /**
     *
     * @return \App\Communicator
     */
    function getCommunicator()
    {
        if(! isset($this->viewVars['communicator']) || (! $this->viewVars['communicator'] instanceof \App\Communicator))
        {
            $this->viewVars['communicator'] = new \App\Communicator();
        }
        
        return $this->viewVars['communicator'];
    }


    /**
     *
     * @return \Zend\Http\PhpEnvironment\Request
     */
    public function getRequest()
    {
        return parent::getRequest();
    }


    /**
     * Get response object
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function getResponse()
    {
        return parent::getResponse();
    }


    /**
     * Translate a string using the given text domain and locale
     *
     * @param string $str
     * @param array $params
     * @param string $textDomain
     * @param string $locale
     * @return string
     */
    function _($str, $params = array(), $textDomain = 'default', $locale = null)
    {
        $sl = $this->getServiceLocator();
        $str = $sl->get('translator')->translate($str, $textDomain, $locale);
        
        if(is_array($params) && count($params))
        {
            array_unshift($params, $str);
            $str = call_user_func_array('sprintf', $params);
        }
        
        return $str;
    }
    
    
    public function onDispatch(Zend\Mvc\MvcEvent $e) {

		
		if($this instanceof App\Controller\UsersController)
		{
            $this->layout('layout/users');
		}
		elseif($this instanceof App\Controller\BackendController)
		{
            $this->layout( 'layout/backend' );
		}        
        elseif($this instanceof App\Controller\AuthenticatedController)
        {
            $this->layout('layout/users');
        }
		
		return parent::onDispatch ( $e );
	}

    /**
     * get user identity
     * 
     * @return Identity
     */
    public function getIdentity()
    {
        $identity = $this->zfcUserAuthentication()->getIdentity();

        return $identity;
    }


    /**
     * has loged
     * 
     * @return bool
     */
    public function hasIdentity(){
        return $this->zfcUserAuthentication()->hasIdentity();
    }


    /**
     * id of current user
     * 
     * @return int
     */
    public function  getUserId(){
        $userId = 0;
        if($this->hasIdentity())
        {
            $identity = $this->getIdentity();
            $userId = $identity->getId();
        }

        return $userId;
    }


    public function  getRole(){
        $roleId = 0;
        if($this->hasIdentity())
        {
            $identity = $this->getIdentity();
            $roleId = $identity->role;            
        }

        return $roleId;
    }

    public function getUserName()
    {
        $zfcUserDisplayName = '';
        if($this->hasIdentity())
        {
            $identity = $this->getIdentity();            
            $displayName = $identity->getDisplayName();
            $email = $identity->getEmail();    
            
            if(!empty($displayName))
                $zfcUserDisplayName = $displayName;
            else
                $zfcUserDisplayName = $email;
        }

        return $zfcUserDisplayName;
    }

}
