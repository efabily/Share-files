<?php

namespace User\Controller;

use Zend\Mvc\Controller\AuthenticatedController;
use Zend\View\Model\ViewModel;
use App;

class UsersController extends App\Controller\AuthenticatedController {	

	public function changePasswordAction()
	{
		$this->loadCommunicator();
		$sl = $this->getServiceLocator();
		$request = $this->request;

		$dbUser = $sl->get('User\Db\User');		
		$uModel = $sl->get('User\Model');

		$currentUserId = $this->getUserId();		

		if ($request->isPost()) {

			if($currentUserId == 4)
			{			
				$this->getCommunicator()->addError('You cannot change password for this user.');
				$this->saveCommunicator($this->getCommunicator());
				$this->redirect()->toRoute('users/wildcard', ['action' =>'list']);
				return;
			}
			
			$params = $request->getPost();
			$params->current_user_id = $currentUserId;
			$flag = $uModel->changePassword($params);

			$this->setCommunicator($uModel->getCommunicator());
			$this->saveCommunicator($uModel->getCommunicator());

			$this->redirect()->toRoute('change-password');
		}			
		
		return new ViewModel($this->viewVars);
	}
}
