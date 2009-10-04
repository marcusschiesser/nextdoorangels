<?php
require_once 'FacebookController.php';

class InfoController extends FacebookController {
    
	function indexAction() {
		$this->requireLogin();
	  	$allConfig = $this->getConfig();
		$this->view->mapRoot = $allConfig['map']['root'];
		
		$this->view->userId = $this->fbUserId;
		
    }
	
	public function aboutAction() {
		
	}
	
	public function involveAction() {
		
	}
    
}