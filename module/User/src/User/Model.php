<?php
namespace User;
use Zend;

class Model extends \App\Model\AbstractModel
{


	
	function changePassword(\Zend\Stdlib\Parameters $params)
	{
		$sl = $this->getServiceLocator();
		$dbUser = $sl->get('User\Db\User');

		$req = array('password', 'new_password');
		if($this->hasEmptyValues($req, $params))		
			return false;	

		try
		{

			$eUser = $dbUser->findByPrimaryKey($params->current_user_id, 'user_id');		
			if(!$eUser)		
			{
				$this->getCommunicator()->addError('Can not find the record you want to edit.');
				return false;
			}


			$hydrator = $sl->get('zfcuser_user_hydrator');

			if(!$hydrator->getCryptoService()->verify($params->password, $eUser->password))
			{
				$this->getCommunicator()->addError('Your current password is wrong.');
				return false;
			}

			$where = array(
				'user_id = ?' => $params->current_user_id
			);

			$data = array();
			$data['password'] = $hydrator->getCryptoService()->create($params->new_password);

			$dbUser->doUpdate($data, $where);

			$this->getCommunicator()->setSuccess('Information saved successfully.');
		}
		catch (\exception $e)
		{
			$this->setException($e);
		}		
		
		return $this->isSuccess();
	}
	

	
	
	function addUser(\Zend\Stdlib\Parameters $params)
	{
		$sl = $this->getServiceLocator();
		$dbUser = $sl->get('User\Db\User');
		
		try
		{		

			if(!$this->validateUser($params, 'add'))
			{
				return false;
			}

			#$this->getCommunicator()->addError('Error 2');

			#$this->getCommunicator()->addError('sub error 1', 'display_name');
			#$this->getCommunicator()->addError('sub error 2', 'display_name');
			
			$data = $params->toArray();
			
			$data['username'] = $params->email;
			$data['role'] = 2;
			$data['state'] = $params->state ? 1 : 0;
			
			// para password
			$hydrator = $sl->get('zfcuser_user_hydrator');
			$password = $hydrator->getCryptoService()->create($params->password);
			$data['password'] = $password;
			
			#
			$dbUser->doInsert($data);

			$this->getCommunicator()->setSuccess('Information saved successfully.');

			#
			$mailer = $sl->get('App\Mailer');
			 
			try
			{

				$name = ($params->display_name) ? $params->display_name : $params->email;
				$msgBody = "
				Hello $name, wolecome to <strong>In The Zone Music</strong><br><br>

				Here you have your credentials to login:<br>
				<strong>Url:</strong> <a href='http://www.inthezonemusic.com'>http://www.inthezonemusic.com</a> <br>
				<strong>Username:</strong> {$params->email}<br>
				<strong>Password:</strong> $params->password<br><br>
				";
			
			   $message = $mailer->prepareMessage($msgBody, null, 'New user account created');
			   $message->addTo($params->email);
			   
			   $transport = $mailer->getTransport($message, 'smtp1', 'no-reply');
			   $transport->send($message);
			}
			catch(\Exception $e)
			{
			   $this->getCommunicator()->setSuccess('Information saved successfully. There was an error sending the welcome message to the user', true);
			}
		}
		catch (\exception $e)
		{
			$this->setException($e);
		}
		
		return $this->isSuccess();
	}



	
	function editUser(\Zend\Stdlib\Parameters $params)
	{
		$sl = $this->getServiceLocator();
		$dbUser = $sl->get('User\Db\User');
		
		try
		{
			if(!$this->validateUser($params, 'edit'))
			{
				return false;
			}	
			
			$data = $params->toArray();
			$data['username'] = $params->email;
			$data['state'] = $params->state ? 1 : 0;
			
			// para password
			if($params->password)
			{
				$hydrator = $sl->get('zfcuser_user_hydrator');
				$password = $hydrator->getCryptoService()->create($params->password);
				$data['password'] = $password;				
			}
			else
			{
				unset($data['password']);
			}
			
			#
			$dbUser->doUpdate($data, function($where) use($params) {
				$where->equalTo('user_id', $params->user_id);
			});

			$this->getCommunicator()->setSuccess('Information saved successfully.');
		}
		catch (\exception $e)
		{
			$this->setException($e);
		}
		
		return $this->isSuccess();
	}
	
	// function deleteUser(\Zend\Stdlib\Parameters $params)
	function deleteUser($id)
	{
		$sl = $this->getServiceLocator();
		$dbUser = $sl->get('User\Db\User');

		try
		{
			$dbUser->doDelete(function($where) use($id) {
				$where->equalTo('user_id', $id);
			});

			$this->getCommunicator()->setSuccess('Record deleted successfully.');
		}
		catch (\exception $e)
		{
			$this->setException($e);
		}

		return $this->isSuccess();
	}



	#############################
	# VALIDATORS
	#############################


	function validateUser(\Zend\Stdlib\Parameters $params, $option)
	{
		$sl = $this->getServiceLocator();
		$dbUser = $sl->get('User\Db\User');

		# validations
		if($option == 'add')
        	$req = array('email', 'password');
        elseif($option == 'edit')
        	$req = array('email');


		if($this->hasEmptyValues($req, $params))		
			return false;

		#
		$validator = new Zend\Validator\EmailAddress();
		if(!$validator->isValid($params->email))		
			$this->getCommunicator()->addError('Please provide a vallid email address', 'email');		

		#
		if($option == 'add')
		{
			$options = array(
				'table' => $dbUser->getTable(),
				'field' => 'email',
				'adapter' => $this->getDbAdapter(),
			);
		}
		elseif($option == 'edit')
		{
			$options = array(
				'table' => $dbUser->getTable(),
				'field' => 'email',
				'adapter' => $this->getDbAdapter(),
				'exclude' => "user_id != {$params->user_id}",
			);
		}
		
		$validator = new Zend\Validator\Db\RecordExists($options);
		if($validator->isValid($params->email))		
			$this->getCommunicator()->addError('Alread exist a user with the given email address', 'email');

		return !$this->isSuccess() ? false : true;
	}
	
	
}