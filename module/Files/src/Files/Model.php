<?php
namespace Files;
use Zend;

class Model extends \App\Model\AbstractModel
{
	
	function addFile(\Zend\Stdlib\Parameters $params)
	{
		$sl = $this->getServiceLocator();
		$dbFile = $sl->get('Files\Db\File');
		$dbFolder = $sl->get('Files\Db\Folder');

		try
		{
			# validations
        	$req = array('file_name', 'file_path');

			if($this->hasEmptyValues($req, $params))
				return false;

			$params->uploaded = date('Y-m-d H:m');
			$data = $params->toArray();
			
			#
			$dbFile->doInsert($data);
			$fileId = $dbFile->getLastInsertValue();

			if($fileId > 0 && isset($params->folder_id))
			{
				$eFolder = $dbFolder->findByPrimaryKey($params->folder_id);
				if($eFolder)
				{
					$dataFolder = array();
					$dataFolder['number_files'] = $eFolder->number_files + 1;

					#
					$dbFolder->doUpdate($dataFolder, function($where) use($params) {
						$where->equalTo('id', $params->folder_id);
					});
				}
			}

			$this->getCommunicator()->setSuccess('Information saved successfully.');
		}
		catch (\exception $e)
		{
			$this->setException($e);
		}
		
		return $this->isSuccess();
	}

	

	function deleteFile($id, $userId)
	{
		$sl = $this->getServiceLocator();
		$dbFile = $sl->get('Files\Db\File');
		$dbFolder = $sl->get('Files\Db\Folder');

		try
		{
			# File
			$rowFile = $dbFile->findByPrimaryKey($id);

			# update folder
			$folderId = $rowFile->folder_id;
			if(!is_null($folderId) && $folderId > 0)
			{				
				$rowFolder = $dbFolder->findByPrimaryKey($folderId);
				$dataFolder = array();
				$dataFolder['number_files'] = $rowFolder->number_files - 1;

				$dbFolder->doUpdate($dataFolder, function($where) use($folderId) {
					$where->equalTo('id', $folderId);
				});
			}

			# update file
			$dbFile->doDelete(function($where) use($id) {
				$where->equalTo('id', $id);
			});
			

			$this->getCommunicator()->setSuccess('Record deleted successfully.');
		}
		catch (\exception $e)
		{
			$this->setException($e);
		}

		return $this->isSuccess();
	}


	function addFolder(\Zend\Stdlib\Parameters $params)
	{
		$sl = $this->getServiceLocator();
		$dbFolder = $sl->get('Files\Db\Folder');

		try
		{
			# validations
        	$req = array('name');

			if($this->hasEmptyValues($req, $params))
				return false;

			$today = date('Y-m-d H:m');

			$params->created_on = $today;
			$params->updated_on = $today;

			$data = $params->toArray();
			
			#
			$dbFolder->doInsert($data);
			$folderId = $dbFolder->getLastInsertValue();

			$this->getCommunicator()->setSuccess('Information saved successfully.');
		}
		catch (\exception $e)
		{
			$this->setException($e);
		}
		
		return $this->isSuccess();
	}


	function editFolder(\Zend\Stdlib\Parameters $params)
	{
		$sl = $this->getServiceLocator();
		$dbFolder = $sl->get('Files\Db\Folder');

		try
		{
			# validations
        	$req = array('name');

			if($this->hasEmptyValues($req, $params))
				return false;

			$today = date('Y-m-d H:m');
			
			$params->updated_on = $today;

			$data = $params->toArray();

			unset($data['folder_id']);
			
			#
			$dbFolder->doUpdate($data, function($where) use($params) {
				$where->equalTo('id', $params->folder_id);
			});


			$this->getCommunicator()->setSuccess('Information saved successfully.');
		}
		catch (\exception $e)
		{
			$this->setException($e);
		}
		
		return $this->isSuccess();
	}


	function deleteFolder($id, $userId)
	{
		$sl = $this->getServiceLocator();
		$dbFolder = $sl->get('Files\Db\Folder');

		try
		{
			$dbFolder->doDelete(function($where) use($id) {
				$where->equalTo('id', $id);
			});

			$this->getCommunicator()->setSuccess('Record deleted successfully.');
		}
		catch (\exception $e)
		{
			$this->setException($e);
		}

		return $this->isSuccess();
	}

	function moveFiles(\Zend\Stdlib\Parameters $params)
	{
		$sl = $this->getServiceLocator();
		$dbFolder = $sl->get('Files\Db\Folder');
		$dbFile = $sl->get('Files\Db\File');
		$dbMove = $sl->get('Files\Db\Move');

		$folderId = $params->folder;
		$aFiles = $params->files;
		try
		{
			# files to move
			$rowsetFiles = $dbFile->findBy(function($where) use($aFiles){
				$where->in('id', $aFiles);
			});

			foreach($rowsetFiles as $row)
			{
				$oldFolderId = $row->folder_id;

				#update old folder
				if(!is_null($oldFolderId))
				{					
					$rowOldFolder = $dbFolder->findByPrimaryKey($oldFolderId);
					$dataUpdateFolder = array();
					$dataUpdateFolder['number_files'] = $rowOldFolder->number_files - 1;				

					$dbFolder->doUpdate($dataUpdateFolder, function($where) use($oldFolderId) {
							$where->equalTo('id', $oldFolderId);
					});
				}

				# update file
				$fileId = $row->id;
				$dataFile = array();

				if($folderId == 0)
					$dataFile['folder_id'] = NULL;
				elseif($folderId > 0)
					$dataFile['folder_id'] = $folderId;

				# update file
				$dbFile->doUpdate($dataFile, function($where) use($fileId) {
					$where->equalTo('id', $fileId);
				});

				# update new folder
				if($folderId > 0)
				{
					$rowFolder = $dbFolder->findByPrimaryKey($folderId);
					$dataFolder = array();
					$dataFolder['number_files'] = $rowFolder->number_files + 1;

					$dbFolder->doUpdate($dataFolder, function($where) use($folderId) {
							$where->equalTo('id', $folderId);
					});
				}


				#create move
				$dataMove = array();
				$dataMove['file_id'] = $fileId;
				$dataMove['user_id'] = $params->user_id;

				if($folderId == 0)
					$dataMove['folder_id'] = NULL;
				elseif($folderId > 0)
					$dataMove['folder_id'] = $folderId;

				if(is_null($oldFolderId))
					$dataMove['old_folder_id'] = NULL;
				elseif($oldFolderId > 0)
					$dataMove['old_folder_id'] = $oldFolderId;

				$dataMove['created_on'] = date('Y-m-d H:m:s');
				
				$dbMove->doInsert($dataMove);

			}// endforeach


			$this->getCommunicator()->setSuccess('Files moved successfully.');

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

}