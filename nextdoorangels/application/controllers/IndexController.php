<?php 
require_once 'FacebookController.php';

class IndexController extends FacebookController {

    function indexAction() {
    	$this->requireLogin();

        $this->view->title = 'Testing a FB App';
		$this->view->shortdescription = 'NextDoorAngels is a facebook application that lets you find social needs in your neighborhood';

		$table = new Model_DbTable_Problems();
		$Id = '2';
       	$problem = $table->getProblemById($table, $Id);
		
		$this->view->name 		= $problem['p_name'];
		$this->view->desc		= $problem['p_description'];
		$this->view->address	= $problem['p_address'];
		$this->view->fb_name	= $problem['fb_name_helpseeker'];

    }
    
}

