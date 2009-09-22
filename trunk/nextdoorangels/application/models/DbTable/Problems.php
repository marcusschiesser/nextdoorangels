<?php

class Model_DbTable_Problems extends Zend_Db_Table
{
	protected $_name = 'problems';
	protected $_primary = 'id';
	
	function getProblemById($table, $id)
	{
			$select = $table->select()->where('id = ?', $id);
			$row = $table->fetchRow($select);
			
			return $row;
	}
	/*
	function insertMarker($court_name, $court_type, $court_description, $court_lat, $court_lng, $user_name, $user_email)
	{
			$data = array(
							'court_name' 			=> $court_name,
							'court_type' 			=> $court_type,
							'court_description' 	=> $court_description,
							'court_lat' 			=> $court_lat,
							'court_lng' 			=> $court_lng,
							'user_name' 			=> $user_name,
							'user_email'			=> $user_email
							 );
			$this->insert($data);
	}
	
	function latestMarkerData($court_lng)
	{
		$select = $this->_db->select()
									->from($this->_name,array('id', 'court_name'))
									->where('court_lng=?',$court_lng);
		$result = $this->getAdapter()->fetchAll($select);		
		return $result;
	}
	

	function getMarkerByLng($table, $lng)
	{
			$select = $table->select()->where('court_lng = ?', $lng);
			$row = $table->fetchRow($select);
			
			return $row;
	}
	*/
}