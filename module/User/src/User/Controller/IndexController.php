<?php

namespace User\Controller;

use Zend\Mvc\Controller\BackendController;
use Zend\View\Model\ViewModel;
use App;

class IndexController extends App\Controller\BackendController {
	
	public function listAction() {
		
		$this->loadCommunicator();
		$sl = $this->getServiceLocator();
		$dbUser = $sl->get('User\Db\User');

		$userId = $this->getUserId();
		
// 		$uModel = $sl->get('User\Model');		
// 		$params = new \Zend\Stdlib\Parameters(); 
// 		$uModel->updatePassword($params);

		$rowset = $dbUser->findAll();

        
        $this->assign('user_id', $userId);
		$this->assign('rowset', $rowset);
		
		return new ViewModel($this->viewVars);
	}	
	
	public function addAction() {
		
		$sl = $this->getServiceLocator();
		$request = $this->request;
		
		$uModel = $sl->get('User\Model');
		
		if ($request->isPost()) {
			
			$params = $request->getPost();
			$flag = $uModel->addUser($params);
			$this->setCommunicator($uModel->getCommunicator());

			if(!$flag)
			{
				;
			}
		}
		
		$data['state'] = 1;
		
		$data['document_title'] = 'Add User';
		$data['document_action'] = $this->url()->fromRoute('users', ['action' => 'add']);

		$this->assign($data);
		
		#
		$view = new ViewModel($this->viewVars);
		$view->setTemplate('user/index/save');
		
		#
		return $view;
	}
	
	
	function editAction()
	{
			
		$sl = $this->getServiceLocator();
		$this->loadCommunicator();
		$request = $this->request;

		$userId = $this->params('id', 0);

		if($userId == 4)
		{			
			$this->getCommunicator()->addError('You can not edit this user.');
			$this->saveCommunicator($this->getCommunicator());
			$this->redirect()->toRoute('users/wildcard', ['action' =>'list']);
			return;
		}

		
		$uModel = $sl->get('User\Model');
		$dbModel = $sl->get('User\Db\User');
		
		$eUser = $dbModel->findByPrimaryKey($userId, 'user_id');
		
		if(!$eUser)
		{			
			$this->getCommunicator()->addError('Can not find the record you want to edit.');
			$this->saveCommunicator($this->getCommunicator());
			$this->redirect()->toRoute('users/wildcard', ['action' =>'list']);
			return;
		}
		
		if ($request->isPost()) {
			$params = $request->getPost();
			$params->user_id = $userId;

			$flag = $uModel->editUser($params);
			$this->setCommunicator($uModel->getCommunicator());
				
			if($flag)
			{
				$this->saveCommunicator($uModel->getCommunicator());
				return $this->redirect()->toRoute('users/wildcard', ['action' =>'edit', 'id' => $userId]);
			}
		}
		
		#
		$viewVars = $eUser->toArray();
		$viewVars['document_title'] = 'Edit User';
		$viewVars['document_action'] = $this->url()->fromRoute('users/wildcard', ['action' => 'edit', 'id' => $userId]);
		$this->assign($viewVars);
		
		#
		$view = new ViewModel($this->viewVars);
		$view->setTemplate('user/index/save');

		return $view;
	}

	function deleteAction()
	{		
		$sl = $this->getServiceLocator();
		$this->loadCommunicator();

		$userId = $this->params('id', 0);
		$currentUserId = $this->getUserId();

		$uModel = $sl->get('User\Model');
		$dbModel = $sl->get('User\Db\User');
		
		$eUser = $dbModel->findByPrimaryKey($userId, 'user_id');
		
		if(!$eUser)
		{
			$this->getCommunicator()->addError('Can not find the record you want to delete.');
			$this->saveCommunicator($this->getCommunicator());

			$this->redirect()->toRoute('users/wildcard', ['action' =>'list']);
			return;
		}

		if($currentUserId == $userId)
		{
			$this->getCommunicator()->addError('You cannot delete your own account..');
			$this->saveCommunicator($this->getCommunicator());

			$this->redirect()->toRoute('users/wildcard', ['action' =>'list']);	
			return;
		}
		
		$flag = $uModel->deleteUser($userId);		
		$this->setCommunicator($uModel->getCommunicator());
				
		if($flag)
		{
			$this->saveCommunicator($uModel->getCommunicator());
			$this->redirect()->toRoute('users/wildcard', ['action' =>'list']);
		}		
		
		$viewVars = $eUser->toArray();
		$this->assign($viewVars);

		#
		$view = new ViewModel($this->viewVars);

		return $view;
	}
	


	function buildParams(array $array)
	{
		$params = new \Zend\Stdlib\Parameters();

		foreach ($array as $key => $value)
		{
			$params->$key = $value;
		}

		return $params;
	}
}

