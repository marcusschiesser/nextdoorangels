<?php 
$err = error_reporting(E_ERROR);
require_once 'facebook/facebook.php';
error_reporting($err);

class ProjectController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }
    
    public function addAction() {
    	$request = $this->getRequest();
        if ($request->isPost()) {
        	// get values
        	$title = $request->getParam('title');
			$description = $request->getParam('description');
			$place = $request->getParam('place');
			$deadline_day = $request->getParam('deadline_day');
			$deadline_month = $request->getParam('deadline_month');
			$send_invitation = $request->getParam('send_invitation');
			// validate values
        	$this->view->errors = array();
			$titleValidator = new Zend_Validate_StringLength(8);
			if (!$titleValidator->isValid($title)) {
				$this->view->errors['title'] = current($titleValidator->getMessages());
			}
            // do the commit
			if(count($this->view->errors) == 0) {
				// TODO: inform user
				return $this->_forward('index');
			}
        } 
    }
	
}

