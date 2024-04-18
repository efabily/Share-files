<?php
namespace Files\Db;
use Zend;

class Folder extends \App\Db\AbstractDb
{
	
	
	protected $tableName = 'folder';

	function getSelectBySearch($search = null)
	{
		$sl = $this->getServiceLocator();

		$dbFolder = $this;
		$dbUser = $sl->get('User\Db\User');		
		$dbFile = $sl->get('Files\Db\File');

		$sql = $dbFolder->getSql();

		$cols = array(
			'id' => new Zend\Db\Sql\Literal('f.id')
			,'name' => new Zend\Db\Sql\Literal('f.name')
			,'date' => new Zend\Db\Sql\Literal('f.updated_on')
			,'display_name' => new Zend\Db\Sql\Literal('u.display_name')
			,'owner_user_id' => new Zend\Db\Sql\Literal('u.user_id')
			,'number' => new Zend\Db\Sql\Literal('f.number_files')			
			,'type'  => new Zend\Db\Sql\Literal("'afolder'")
			,'folder_name'  => new Zend\Db\Sql\Literal("''")
			,'folder_id' => new Zend\Db\Sql\Literal("''")
		);

		$select = new \Zend\Db\Sql\Select();

		$select->columns($cols);

		$select->from(array('f' => $dbFolder->getTable()));
		$select->join(array('u' => $dbUser->getTable()), 'u.user_id = f.user_id', array());

		if(!empty($search))
		{
			$select->where(function($where) use($search){
				$where->like('f.name', '%'.$search.'%');
				$where->or->like('u.display_name', '%'.$search.'%');
			});
		}

		$select->order('type');
		$select->order('date DESC');

		$selectTwo = $dbFile->getSelectBySearch($search);

		$select->combine($selectTwo);

		$sql = new \Zend\Db\Sql\Select();
		$selectUnion = $sql->from(array('resultTbl' => $select));

		$selectUnion->order('type');
		$selectUnion->order('date desc');

		return $selectUnion;
	}


	function getBySearch($search = null)
	{
		$sl = $this->getServiceLocator();

		$dbFolder = $this;
		$dbUser = $sl->get('User\Db\User');

		$sql = $dbFolder->getSql();

		$cols = array(
			'id' => new Zend\Db\Sql\Literal('f.id')
			,'name' => new Zend\Db\Sql\Literal('f.name')
			,'date' => new Zend\Db\Sql\Literal('f.updated_on')
			,'display_name' => new Zend\Db\Sql\Literal('u.display_name')
			,'owner_user_id' => new Zend\Db\Sql\Literal('u.user_id')
			,'number' => new Zend\Db\Sql\Literal('f.number_files')			
		);

		$select = new \Zend\Db\Sql\Select();

		$select->columns($cols);

		$select->from(array('f' => $dbFolder->getTable()));
		$select->join(array('u' => $dbUser->getTable()), 'u.user_id = f.user_id', array());

		if(!empty($search))
		{
			$select->where(function($where) use($search){
				$where->like('f.name', '%'.$search.'%');
				$where->or->like('u.display_name', '%'.$search.'%');
			});
		}

		$select->order('f.updated_on desc');
		// $this->debugSql($select);exit;

		return $select;
	}


	function folderBelongstoUser($folderId, $userId)
	{
		$sl = $this->getServiceLocator();
		$dbFolder = $this;
		$dbUser = $sl->get('User\Db\User');


		$sql = $dbFolder->getSql();

		$cols = array(
			'id' => new Zend\Db\Sql\Literal('f.id')
			,'name' => new Zend\Db\Sql\Literal('f.name')
			,'created_on' => new Zend\Db\Sql\Literal('f.created_on')
			,'updated_on' => new Zend\Db\Sql\Literal('f.updated_on')
			,'user_id' => new Zend\Db\Sql\Literal('f.user_id')
		);

		$select = new \Zend\Db\Sql\Select();

		$select->columns($cols);

		$select->from(array('f' => $dbFolder->getTable()));
		$select->join(array('u' => $dbUser->getTable()), 'u.user_id = f.user_id', array());


		$select->where(function($where) use($userId, $folderId){
			$where->equalTo('u.user_id', $userId);
			$where->equalTo('f.id', $folderId);
		});

		return $this->executeCustomSelect($select)->current();
	}






}