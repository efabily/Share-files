<?php

namespace Auth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use App;

class IndexController extends App\Controller\PublicController {
	
	
	public function loginAction() {
		$this->layout ( 'layout/login' );
	}
	
	public function passwordReminderAction() {
		$this->layout ( 'layout/login' );
	}

	public function autoLoginAction()
	{
		
	}
	
	public function initAction() {
		
		if (!$this->hasIdentity()) {
			return $this->redirect()->toUrl('zfcuser/logout');
		}
		
		$identity = $this->getIdentity();
	
		// verificar el rol para redireccionar donde corresponda
		$sl = $this->getServiceLocator();
		$dbUser = $sl->get('User\Db\User');

		$eUser = $dbUser->findByPrimarykey($identity->getId(), 'user_id');
		if(!$eUser)
		{
			return $this->redirect()->toUrl('zfcuser/logout');
		}

		$identity->role = $eUser->role; // usuarios
		$this->zfcUserAuthentication()->getAuthService()->getStorage()->write($identity);
		
		$this->redirect()->toRoute('files', array('action' => 'list'));
	}

}
