<?php 
$err = error_reporting(E_ERROR);
require_once 'facebook/facebook.php';
error_reporting($err);

class ProjectController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }
    
    public function addAction() {
        $this->view->errors = array();
        $request = $this->getRequest();
        $doit = $request->getParam('do-it');
        if (isset($doit)) {
            // get values
            $title = $request->getParam('title');
            $description = $request->getParam('description');
            $place = $request->getParam('place');
            $deadline_day = $request->getParam('deadline_day');
            $deadline_month = $request->getParam('deadline_month');
            $send_invitation = $request->getParam('send_invitation');
            // validate values
            $titleValidator = new Zend_Validate();
            $titleValidator->addValidator( new Zend_Validate_StringLength(8))->addValidator( new Zend_Validate_Alnum());
            if (!$titleValidator->isValid($title)) {
                $this->view->errors['Title'] = current($titleValidator->getMessages());
            }
            $descriptionValidator = new Zend_Validate_StringLength(8);
            if (!$descriptionValidator->isValid($description)) {
                $this->view->errors['Description'] = current($descriptionValidator->getMessages());
            }
            $placeValidator = new Zend_Validate_StringLength(3);
            if (!$placeValidator->isValid($place)) {
                $this->view->errors['Place'] = current($placeValidator->getMessages());
            }
            // do the commit
            if (count($this->view->errors) == 0) {
                // commit project
                
                // TODO: inform user
				$this->_helper->FlashMessenger('You successfully created a social project. We wish you a lot of success.');
                return $this->_forward('index', 'index');
            }
        }
    }
    
}

