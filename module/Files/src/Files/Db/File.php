<?php
namespace Files\Db;
use Zend;

class File extends \App\Db\AbstractDb
{
	
	
	protected $tableName = 'file';
	


	function getSelectBySearch($search = null, $folderId = null)
	{
		$sl = $this->getServiceLocator();

		$dbFile = $this;
		$dbUser = $sl->get('User\Db\User');
		$dbFolder = $sl->get('Files\Db\Folder');

		$sql = $dbFile->getSql();

		$cols = array(
			'id' => new Zend\Db\Sql\Literal('f.id')
			,'name' => new Zend\Db\Sql\Literal('f.file_name')
			,'date' => new Zend\Db\Sql\Literal('f.uploaded')
			,'display_name' => new Zend\Db\Sql\Literal('u.display_name')
			,'owner_user_id' => new Zend\Db\Sql\Literal('u.user_id')
			,'number' => new Zend\Db\Sql\Literal("'0'")
			,'type'  => new Zend\Db\Sql\Literal("'bfile'")			
		);

		if(!empty($search))
		{
			$cols['folder_name'] = new Zend\Db\Sql\Literal('fo.name');
			$cols['folder_id'] = new Zend\Db\Sql\Literal('fo.id');
		}
		else
		{
			$cols['folder_name'] = new Zend\Db\Sql\Literal("''");
			$cols['folder_id'] = new Zend\Db\Sql\Literal("''");
		}


		$select = new \Zend\Db\Sql\Select();

		$select->columns($cols);

		$select->from(array('f' => $dbFile->getTable()));
		$select->join(array('u' => $dbUser->getTable()), 'u.user_id = f.user_id', array());


		
		if(!is_null($folderId) &&  !empty($search))
		{
			$select->join(array('fo' => $dbFolder->getTable()), 'f.folder_id = fo.id', array(), $select::JOIN_LEFT);

			$select->where(function($where) use($folderId, $search) {
				$where->equalTo('f.folder_id', $folderId);

				$where->NEST // start braket

				->like('f.file_name', '%'.$search.'%')
				->OR->like('u.display_name', '%'.$search.'%')

				->UNNEST; // close braket
			});
		}
		elseif(!is_null($folderId))
		{
			$select->where(function($where) use($folderId){
				$where->equalTo('f.folder_id', $folderId);
			});		
		}
		elseif(!empty($search))
		{			
				$select->join(array('fo' => $dbFolder->getTable()), 'f.folder_id = fo.id', array(), $select::JOIN_LEFT);

				$select->where(function($where) use($search){
					$where->like('f.file_name', '%'.$search.'%');
					$where->or->like('u.display_name', '%'.$search.'%');
				});			
		}
		else
		{
			$select->where('f.folder_id IS NULL');
		}

		$select->order('type');
		$select->order('date DESC');
		
		return $select;
	}



	function fileBelongstoUser($fileId, $userId)
	{
		$sl = $this->getServiceLocator();
		$dbFile = $this;
		$dbUser = $sl->get('User\Db\User');


		$sql = $dbFile->getSql();

		$cols = array(
			'file_id' => new Zend\Db\Sql\Literal('f.id')
			,'file_name' => new Zend\Db\Sql\Literal('f.file_name')
			,'file_uploaded' => new Zend\Db\Sql\Literal('f.uploaded')
			,'file_path' => new Zend\Db\Sql\Literal('f.file_path')
			,'file_size' => new Zend\Db\Sql\Literal('f.size')
		);

		$select = new \Zend\Db\Sql\Select();

		$select->columns($cols);

		$select->from(array('f' => $dbFile->getTable()));
		$select->join(array('u' => $dbUser->getTable()), 'u.user_id = f.user_id', array());


		$select->where(function($where) use($userId, $fileId){
			$where->equalTo('u.user_id', $userId);
			$where->equalTo('f.id', $fileId);
		});

		$select->order('f.uploaded desc');

		return $this->executeCustomSelect($select)->current();
	}
}