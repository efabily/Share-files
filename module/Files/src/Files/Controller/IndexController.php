<?php

namespace Files\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use App;

class IndexController extends App\Controller\UsersController {

    /* */		
	
	public function listAction() {

		$this->loadCommunicator();
		$sl = $this->getServiceLocator();
		$request = $this->request;
		$dbFolder = $sl->get('Files\Db\Folder');
		$dbFile = $sl->get('Files\Db\File');		

		#
		$folderId = $this->params('folder', null);

		$userId = $this->getUserId();
		$role = $this->getRole();

		$search = "";
		if ($request->isPost())
		{
			$params = $request->getPost();
			$search = $params->search;
		}
		else
		{
			$search = $this->params('search', '');
		}		

		# route add folder
		$urlAddFolder = $this->url()->fromRoute('files/wildcard', ['action' =>'add-folder']);

		$page_title = '';
		if(!is_null($folderId))
		{

			# folder
			$rowFolder = $dbFolder->findByPrimaryKey($folderId);
			if(!$rowFolder)
			{
				$this->getCommunicator()->addError('Cannot find the folder you want to filter.');
				$this->saveCommunicator($this->getCommunicator());
				return $this->redirect()->toRoute('files/wildcard', ['action' =>'list']);
			}

			#
			$select = $dbFile->getSelectBySearch($search, $folderId);

			$urlList = $this->url()->fromRoute('files/wildcard', ['action' =>'list']);

			$page_title = "<a href='{$urlList}' style='color: #337ab7;text-decoration: underline;' >Folders And Files</a>";
			$page_title = $page_title.'/'.$rowFolder->name;
			
			$this->assign('folder_id', $folderId);

			$urlUpload = $this->url()->fromRoute('files/wildcard', ['action' =>'upload', 'id' => $folderId]);
			$urlSearch = $this->url()->fromRoute('files/wildcard', ['action' =>'list', 'folder' => $folderId]);

			$tableTitle = "Files Names";

			# route
			if(!empty($search))
			{
				$route = array(
			    	'route' => 'files/wildcard'
			         ,'route_params' => array('action' => 'list', 'folder' => $folderId, 'search' => $search)
			     );

				$urlBack = $this->url()->fromRoute('files/wildcard', ['action' =>'list', 'folder' => $folderId, 'search' => $search]);
			}
			else
			{
				$route = array(
			         'route' => 'files/wildcard'
			         ,'route_params' => array('action' => 'list', 'folder' => $folderId)
			     );

				$urlBack = $this->url()->fromRoute('files/wildcard', ['action' =>'list', 'folder' => $folderId]);
			}
		}
		else
		{
			$select = $dbFolder->getSelectBySearch($search);
			$page_title = "Folders And Files";

			$urlUpload = $this->url()->fromRoute('files/wildcard', ['action' =>'upload']);
			$urlSearch = $this->url()->fromRoute('files/wildcard', ['action' =>'list']);

			$tableTitle = "Folder / Files Names";

			# route
			if(!empty($search))
			{
				$route = array(
			    	'route' => 'files/wildcard'
			         ,'route_params' => array('action' => 'list', 'search' => $search)
			     );

				$urlBack = $this->url()->fromRoute('files/wildcard', ['action' =>'list', 'search' => $search]);
			}
			else
			{
				$route = array(
			         'route' => 'files/wildcard'
			         ,'route_params' => array('action' => 'list')
			     );

				$urlBack = $this->url()->fromRoute('files/wildcard', ['action' =>'list']);
			}
		}

		$this->assign('current_folder_id', $folderId);

		
		$urlMoveFiles = $this->url()->fromRoute('files/wildcard', ['action' =>'move-files']);

		$this->assign('table_title', $tableTitle);

		$this->assign('page_title', $page_title);
		
		$this->assign('url_upload', $urlUpload);
		$this->assign('url_move_files', $urlMoveFiles);
		$this->assign('url_search', $urlSearch);	

		$adapter = new \Zend\Paginator\Adapter\DbSelect($select, $sl->get('adapter'));
		$paginator = new \Zend\Paginator\Paginator($adapter);
		$paginator->setCurrentPageNumber((int) $this->params()->fromRoute('page', 1));
		$paginator->setItemCountPerPage(20);


		$routeBase64 = base64_encode($urlBack);
		$this->assign('route_base_64', $routeBase64);		
		
		$this->assign('url_add_folder', $urlAddFolder);
		$this->assign('route', $route);		

		$this->assign('search', $search);
		$this->assign('user_id', $userId);
		$this->assign('role', $role);
		
		$this->assign('rowset', $paginator);
		return new ViewModel($this->viewVars);
	}
	
	
	public function uploadAction() {
		$this->loadCommunicator();
		$sl = $this->getServiceLocator();
		$dbFolder = $sl->get('Files\Db\Folder');

		$this->layout('layout/blank');

		$folderId = $this->params('id', 0);

		if($folderId > 0)
		{
			$eFolder = $dbFolder->findByPrimaryKey($folderId);

			if(!$eFolder)
			{			
				$this->getCommunicator()->addError('Cannot find the folder for the you want uploads files.');
				$this->saveCommunicator($this->getCommunicator());
				$this->redirect()->toRoute('files/wildcard', ['action' =>'list']);
				return;
			}

			$viewVars = $eFolder->toArray();
			$viewVars['document_title'] = "Upload files to the folder <strong>{$eFolder->name}</strong>";
			$viewVars['document_action'] = $this->url()->fromRoute('files/wildcard', ['action' => 
					'ajax-upload', 'id' => $folderId]);

			$this->assign($viewVars);
		}
		else
		{

			$this->assign('document_title', 'Upload files');

			$document_action = $this->url()->fromRoute('files/wildcard', ['action' => 'ajax-upload']);
			$this->assign('document_action', $document_action);
		}


		#
		$view = new ViewModel($this->viewVars);
		
		#
		return $view;				
	}

	public function ajaxUploadAction()
	{
		$sl = $this->getServiceLocator();		
		$params = $sl->get('\Zend\Stdlib\Parameters'); 

		$folderId = $this->params('id', 0);

		$uModel = $sl->get('Files\Model');
		$params->user_id = $this->getUserId();

		if($folderId > 0)
		{
			$params->folder_id = $folderId;
		}
        

		// 5 minutes execution time
		@set_time_limit(0);

		ini_set('memory_limit', -1);

		// Uncomment this one to fake upload time
		// usleep(5000);

		// Settings
		// $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";		
		$uploadsDir = "uploads";
		$targetDir = PUBLIC_DIRECTORY. DIRECTORY_SEPARATOR .$uploadsDir;	

		// Remove old files
		$cleanupTargetDir = false;

		// Temp file age in seconds
		$maxFileAge = 5 * 3600; 

		// Create target dir
		if (!file_exists($targetDir)) {
			@mkdir($targetDir);
		}

		// Get a file name
		if (isset($_REQUEST["name"])) {
			$fileName = $_REQUEST["name"];
		} elseif (!empty($_FILES)) {
			$fileName = $_FILES["file"]["name"];
		} else {
			$fileName = uniqid("file_");
		}

		############Params############		
		$params->file_name = $fileName;

		$uniqueFileName = $this->getUniqueFileName();
		$fileName = $uniqueFileName.'-'.$fileName;
		$params->unique_file_name = $fileName;


		$filePath = $targetDir. DIRECTORY_SEPARATOR . $fileName;

		############Params############
		$params->file_path = $uploadsDir . DIRECTORY_SEPARATOR . $fileName;
			

		// Chunking might be enabled
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


		// Remove old temp files	
		if ($cleanupTargetDir) {
			if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
			}

			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$filePath}.part") {
					continue;
				}

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
					@unlink($tmpfilePath);
				}
			}
			closedir($dir);
		}	


		// Open temp file
		if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		if (!empty($_FILES)) {
			if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}

			// Read binary input stream and append it to temp file
			if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		} else {	
			if (!$in = @fopen("php://input", "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		}

		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off 
			rename("{$filePath}.part", $filePath);
		}

		############Params############		
		$uModel->addFile($params);

		// Return Success JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}

	
	public function downloadAction() {

		$sl = $this->getServiceLocator();
		$dbFile = $sl->get('Files\Db\File');
		$fModel = $sl->get('Files\Model');

		$fileId = $this->params('id', 0);
		$userId = $this->getUserId();
		
		$row = $dbFile->findByPrimaryKey($fileId);
		if(!$row)
		{			
			$this->getCommunicator()->addError('Can not find the record you want to download.');
			$this->saveCommunicator($this->getCommunicator());			
			return $this->redirect()->toRoute('files/wildcard', ['action' =>'list']);
		}

		try
		{
			$file = $row->file_name;			
			$path = PUBLIC_DIRECTORY. DIRECTORY_SEPARATOR .$row->file_path;	

			$type = '';

			if (is_file($path)) {
	 			$size = filesize($path);

	 			if (function_exists('mime_content_type')) {
	 				$type = mime_content_type($path);
	 			} else if (function_exists('finfo_file')) {
	 				$info = finfo_open(FILEINFO_MIME);
	 				$type = finfo_file($info, $path);
	 				finfo_close($info);
	 			} 			

	 			// Definir headers
	 			header("Content-Type: $type");
	 			header("Content-Disposition: attachment; filename=$file");
	 			header("Content-Transfer-Encoding: binary");
	 			header("Content-Length: " . $size);

	 			// Descargar archivo
	 			readfile($path);exit;
			} else {
		 		$this->getCommunicator()->addError('This file no exist.');
				$this->saveCommunicator($this->getCommunicator());			
				return $this->redirect()->toRoute('files/wildcard', ['action' =>'list']);			
			}

		}
		catch (\exception $e)
		{
			$msgException = $e->getMessage();

			$this->getCommunicator()->addError($msgException);
			$this->saveCommunicator($this->getCommunicator());

			return $this->redirect()->toRoute('files/wildcard', ['action' =>'exception']);
		}

	}

	public function deleteAction() 
	{	
		$sl = $this->getServiceLocator();																								
		$dbFile = $sl->get('Files\Db\File');
		$fModel = $sl->get('Files\Model');																															

		$fileId = $this->params('id', 0);																									
		$userId = $this->getUserId();
		$role = $this->getRole();

		$back = $this->params('back', '');
		$urlBack = base64_decode($back);


		if($role == 1)
		{
			$row = $dbFile->findByPrimaryKey($fileId);
		}
		elseif($role == 2)
		{
			$row = $dbFile->fileBelongstoUser($fileId, $userId);
			if(!$row)
			{
				$this->getCommunicator()->addError('You cannot delete files uploaded by other users.');
				$this->saveCommunicator($this->getCommunicator());
				return $this->redirect()->toUrl($urlBack);
			}
		}

		try
		{				

        # delete file
		$filePath = PUBLIC_DIRECTORY. DIRECTORY_SEPARATOR .$row->file_path;		

		#
			if($fModel->deleteFile($fileId, $userId))
			{
				// Create target dir
				if (file_exists($filePath))
				{
					unlink($filePath);
				}

				$this->saveCommunicator($fModel->getCommunicator());
				return $this->redirect()->toUrl($urlBack);
			}
		}
		catch (\exception $e)
		{
			$msgException = $e->getMessage();

			$this->getCommunicator()->addError($msgException);
			$this->saveCommunicator($this->getCommunicator());

			return $this->redirect()->toRoute('files/wildcard', ['action' =>'exception']);
		}
		exit;
	}

	public function addFolderAction()
	{
		$this->loadCommunicator();
		$sl = $this->getServiceLocator();
		$request = $this->request;

		$this->layout('layout/blank');

		$fModel = $sl->get('Files\Model');

		$userId = $this->getUserId();

		$data['document_title'] = 'Add Folder';
		$data['document_action'] = $this->url()->fromRoute('files', ['action' => 'add-folder']);

		if ($request->isPost()) {
			
			$params = $request->getPost();
			$params->user_id = $userId;
			$flag = $fModel->addFolder($params);
			$this->setCommunicator($fModel->getCommunicator());

			if($flag)
			{
				$this->saveCommunicator($fModel->getCommunicator());
				return $this->redirect()->toRoute('files/wildcard', ['action' =>'add-folder']);
			}
		}

		#
		$this->assign($data);
		
		#
		$view = new ViewModel($this->viewVars);
		$view->setTemplate('files/index/save');
		
		#
		return $view;
	}

	public function editFolderAction()
	{
		$this->loadCommunicator();
		$sl = $this->getServiceLocator();
		$request = $this->request;

		$this->layout('layout/blank');

		$fModel = $sl->get('Files\Model');
		$dbFolder = $sl->get('Files\Db\Folder');

		$folderId = $this->params('id', 0);

		$eFolder = $dbFolder->findByPrimaryKey($folderId);
		if(!$eFolder)
		{			
			$this->getCommunicator()->addError('Can not find the record you want to edit.');
			$this->saveCommunicator($this->getCommunicator());
			$this->redirect()->toRoute('files/wildcard', ['action' =>'folders-files']);
			return;
		}	

		if ($request->isPost()) {
			
			$params = $request->getPost();			
			$params->folder_id = $folderId;

			$flag = $fModel->editFolder($params);
			$this->setCommunicator($fModel->getCommunicator());

			if($flag)
			{
				$this->saveCommunicator($fModel->getCommunicator());
				return $this->redirect()->toRoute('files/wildcard', ['action' =>'edit-folder', 'id' => $folderId]);
			}
		}

		#
		$viewVars = $eFolder->toArray();
		$viewVars['document_title'] = 'Edit Folder';
		$viewVars['document_action'] = $this->url()->fromRoute('files/wildcard', ['action' => 'edit-folder', 'id' => $folderId]);

		$this->assign($viewVars);
		
		#
		$view = new ViewModel($this->viewVars);
		$view->setTemplate('files/index/save');
		
		#
		return $view;
	}


	public function deleteFolderAction() 
	{
		$sl = $this->getServiceLocator();
		$dbFolder = $sl->get('Files\Db\Folder');
		$fModel = $sl->get('Files\Model');

		$folderId = $this->params('id', 0);

		# back
		$back = $this->params('back', '');
		$urlBack = base64_decode($back);

		$userId = $this->getUserId();
		$role = $this->getRole();

		if($role == 1)
		{
			$row = $dbFolder->findByPrimaryKey($folderId);
		}
		elseif($role == 2)
		{
			$row = $dbFolder->folderBelongstoUser($folderId, $userId);
			if(!$row)
			{
				$this->getCommunicator()->addError('You cannot delete folders created by other users.');
				$this->saveCommunicator($this->getCommunicator());				
				return $this->redirect()->toUrl($urlBack);
			}
		}

		if($row->number_files > 0)
		{
			$this->getCommunicator()->addError('You cannot delete non empty folders.');
			$this->saveCommunicator($this->getCommunicator());				
			return $this->redirect()->toUrl($urlBack);
		}

		#
		if($fModel->deleteFolder($folderId, $userId))
		{
			$this->saveCommunicator($fModel->getCommunicator());
			return $this->redirect()->toUrl($urlBack);
		}

		exit;
	}


	public function moveFilesAction()
	{
		$this->loadCommunicator();

		$sl = $this->getServiceLocator();
		$request = $this->request;

		$fModel = $sl->get('Files\Model');
		$dbFolder = $sl->get('Files\Db\Folder');
		$dbFile = $sl->get('Files\Db\File');

		$this->layout('layout/blank');

		# current user
		$userId = $this->getUserId();
		
		$back = $this->params()->fromQuery('back', '');
		if(empty($back))
			$back = $this->params('back', '');

		$urlBack = base64_decode($back);
		
		$files = $this->params()->fromQuery('files', '');
		if(empty($files))
			$files = $this->params('files', '');

		if(empty($files))
		{			
			$this->getCommunicator()->addError('You have to select a list one file to move.');
			$this->saveCommunicator($this->getCommunicator());			
			return $this->redirect()->toUrl($urlBack);
		}

		#files to move
		$search = "";
		$page = $this->params('page', 1);
		if ($request->isPost())
		{
			$params = $request->getPost();
			$search = $params->search;

			if($params->option == 'save')
			{

				$files = $params->files;

				# last ","
				$files = substr($files, 0, -1);

				# files
				$aFiles = explode(',', $files);				

				$rr = true;
				if(count($aFiles) <= 0)
				{					
					$this->getCommunicator()->addError('You have to select a list one file to move.');
					$rr = false;
				}

				#folder
				if(!isset($params->folder))
				{					
					$this->getCommunicator()->addError('You must select a folder.');
					$rr = false;
				}

				if($rr)
				{
					$params->files = $aFiles;
					$params->user_id = $userId;
					$currentDocumentAction = $params->current_document_action;
					unset($params->current_document_action);

					$flag = $fModel->moveFiles($params);
					if($flag)
					{
						$this->saveCommunicator($fModel->getCommunicator());	
						return $this->redirect()->toUrl($currentDocumentAction);
					}
				}
				
			}
			
		}
		else
		{
			$search = $this->params('search', '');
		}

		
		
		$document_action = '';
		if(!empty($search))
		{
			$route = array(
				'route' => 'files/wildcard'
			    ,'route_params' => array('action' => 'move-files', 'back' => $back, 'files' => $files, 'search' => $search)
			);

			$document_action = $this->url()->fromRoute('files/wildcard', ['action' => 'move-files', 'back' => $back, 'files' => $files, 'search' => $search, 'page' => $page]);
		}
		else
		{
			$route = array(
				'route' => 'files/wildcard'
			    ,'route_params' => array('action' => 'move-files', 'back' => $back, 'files' => $files)
			);

			$document_action = $this->url()->fromRoute('files/wildcard', ['action' => 'move-files', 'back' => $back, 'files' => $files, 'page' => $page]);
		}

		$this->assign('route', $route);
		$this->assign('document_action', $document_action);		

		$this->assign('selected_files', $files);
		$this->assign('search', $search);		


		$select = $dbFolder->getBySearch($search);
		$adapter = new \Zend\Paginator\Adapter\DbSelect($select, $sl->get('adapter'));
		$paginator = new \Zend\Paginator\Paginator($adapter);
		$paginator->setCurrentPageNumber((int) $this->params()->fromRoute('page', 1));
		$paginator->setItemCountPerPage(3);

		$this->assign('rowset', $paginator);

		#all folders
		$rowsetFolder = $dbFolder->findAll();
		$this->assign('folders', $rowsetFolder);

		#
		$this->assign('document_title', 'Move files');		
		
		#
		$view = new ViewModel($this->viewVars);
		
		#
		return $view;
	}


	public function deleteFilesAction() 
	{
		$sl = $this->getServiceLocator();																								
		$dbFile = $sl->get('Files\Db\File');
		$fModel = $sl->get('Files\Model');

		# current user
		$userId = $this->getUserId();
		
		# url back
		$back = $this->params()->fromQuery('back', '');
		if(empty($back))
			$back = $this->params('back', '');

		$urlBack = base64_decode($back);


		# selected files
		$files = $this->params()->fromQuery('files', '');
		if(empty($files))
			$files = $this->params('files', '');

		$aFiles = array();
		# last ","
		$files = substr($files, 0, -1);

		if(!empty($files))
			$aFiles = explode(',', $files);


		if(empty($files) && count($aFiles) <= 0 )
		{
			$this->getCommunicator()->addError('You have to select a list at last one file to delete.');
			$this->saveCommunicator($this->getCommunicator());
			return $this->redirect()->toUrl($urlBack);
		}

		# Role
		$role = $this->getRole();		

		try{
			$rowsetFiles = $dbFile->findBy(function($where) use($aFiles) {
				$where->in('id', $aFiles);
			});
			
			$filesOther = 0;
			$filesOwn = 0;

			foreach($rowsetFiles as $row)
			{
				$d = true;
				if($role == 2)
				{
					$rowOnw = $dbFile->fileBelongstoUser($row->id, $userId);
					if($rowOnw)
					{
						$filesOwn = $filesOwn + 1;
					}
					else
					{
						$d = false;
						$filesOther = $filesOther + 1;
					}
				}

				# delete file
				if($d)
				{
					$filePath = '';
					$filePath = PUBLIC_DIRECTORY. DIRECTORY_SEPARATOR .$row->file_path;

					if($fModel->deleteFile($row->id, $userId))
					{
						// Create target dir
						if (file_exists($filePath))
						{
							unlink($filePath);
						}
					}
				}
			}

		}
		catch (\exception $e)
		{
			$msgException = $e->getMessage();

			$this->getCommunicator()->addError($msgException);
			$this->saveCommunicator($this->getCommunicator());

			return $this->redirect()->toRoute('files/wildcard', ['action' =>'exception']);
		}		

		$type = '';
		$msssage = '';
		if($role == 1)
		{
			$type = 'success';
			if(count($aFiles) > 1)
				$msssage = 'Records successfully deleted.';
			else
				$msssage = 'Record successfully deleted.';
		}
		else
		{
			if($filesOwn > 0 && $filesOther <= 0)
			{
				$type = 'success';
				if(count($aFiles) > 1)
					$msssage = 'Records deleted successfully.';
				else
					$msssage = 'Record deleted successfully.';
			}
			elseif($filesOwn <= 0 && $filesOther > 0)
			{
				$type = 'error';
				$msssage = 'You cannot delete files uploaded by other users.';
			} 
			elseif($filesOwn > 0 && $filesOther > 0)
			{
				$type = 'warnning';
				$msssage = 'Only files uploaded by you were deleted. You can\' remove files uploaded by other users';
			}
		}		

		#
		if($type == 'error')
			$this->getCommunicator()->addError($msssage);
		elseif($type == 'warnning')
			$this->getCommunicator()->setSuccess($msssage, 1);
		else
			$this->getCommunicator()->setSuccess($msssage);


		$this->saveCommunicator($this->getCommunicator());
		return $this->redirect()->toUrl($urlBack); exit;
	}

	public function downloadFilesAction() {

		$sl = $this->getServiceLocator();
		$dbFile = $sl->get('Files\Db\File');
		$fModel = $sl->get('Files\Model');

		# current user
		$userId = $this->getUserId();
		
		# url back
		$back = $this->params()->fromQuery('back', '');
		if(empty($back))
			$back = $this->params('back', '');

		$urlBack = base64_decode($back);

		# selected files
		$files = $this->params()->fromQuery('files', '');
		if(empty($files))
			$files = $this->params('files', '');

		$aFiles = array();
		# last ","
		$files = substr($files, 0, -1);		
		

		if(!empty($files))
			$aFiles = explode(',', $files);

		if(empty($files) && count($aFiles) <= 0 )
		{
			$this->getCommunicator()->addError('You have to select a list at last one file to the download.');
			$this->saveCommunicator($this->getCommunicator());
			return $this->redirect()->toUrl($urlBack);
		}

		try
		{
			$rowsetFiles = $dbFile->findBy(function($where) use($aFiles) {
				$where->in('id', $aFiles);
			});

			# 1) crate temp directory with rand name
			$folderName = $this->getUniqueFileName(1);
			$pathFolder = PUBLIC_DIRECTORY. '/uploads/'.$folderName;
			mkdir($pathFolder, 0755);			

			# 2) copy files
			foreach($rowsetFiles as $row)
			{
				$file = $row->file_name;
				$pathFile = PUBLIC_DIRECTORY. DIRECTORY_SEPARATOR .$row->file_path;

				if (is_file($pathFile)) 
				{
					copy($pathFile, $pathFolder.'/'.$file);				
				}
			}	

			# 3) compress directory using rand name
			$zipname = PUBLIC_DIRECTORY.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR .$folderName.".zip";

			$filter = new \Zend\Filter\Compress(array(
				'adapter' => 'Zip',
				'options' => array(
			        'archive' => $zipname
			    ),
			));

			$compress = $filter->filter($pathFolder);
			# 4) delete files and folder
				// delete files

			foreach($rowsetFiles as $row)
			{			
				$deleteFile = $pathFolder.'/'.$row->file_name;
				if (is_file($deleteFile)) 
				{
					unlink($deleteFile);
				}
			}

			// delete folder
			rmdir($pathFolder);
			# 5) ddownload the zip

			$type = '';	
			$userName = $this->getUserName();	
			$folderZipName = $userName.".zip";

			if (is_file($zipname)) {
	 			$size = filesize($zipname);

	 			if (function_exists('mime_content_type')) {
	 				$type = mime_content_type($zipname);
	 			} else if (function_exists('finfo_file')) {
	 				$info = finfo_open(FILEINFO_MIME);
	 				$type = finfo_file($info, $zipname);
	 				finfo_close($info);
	 			} 			

	 			// Definir headers
	 			header("Content-Transfer-Encoding: binary"); 			
	 			header("Content-type: $type");
	 			header("Content-Disposition: attachment; filename=$folderZipName"); 			
	 			header("Content-Length: " . $size);

	 			// Descargar archivo
	 			readfile($zipname);exit;
			} else {
		 		$this->getCommunicator()->addError('This file no exist.');
				$this->saveCommunicator($this->getCommunicator());
				return $this->redirect()->toRoute('files/wildcard', ['action' =>'list']);
			}
		}
		catch (\exception $e)
		{
			$msgException = $e->getMessage();

			$this->getCommunicator()->addError($msgException);
			$this->saveCommunicator($this->getCommunicator());

			return $this->redirect()->toRoute('files/wildcard', ['action' =>'exception']);
		}
	}

	public function exceptionAction() 
	{
		$sl = $this->getServiceLocator();
		$this->loadCommunicator();		

		#
		$view = new ViewModel($this->viewVars);		

		#
		return $view;
	}

	#############################
	function getUniqueFileName($folder = null)
	{
		$userId = $this->getUserId();

		if(!is_null($folder))
			$unique_file_name = $userId.'-FF-'.str_replace('%', '', str_replace('.', '', str_replace(' ', '', rawurlencode(microtime()))));
		else
			$unique_file_name = $userId.'-'.str_replace('%', '', str_replace('.', '', str_replace(' ', '', rawurlencode(microtime()))));
		
		return $unique_file_name;
	}
	
}