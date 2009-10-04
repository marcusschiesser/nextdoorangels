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
		$this->requireLogin();
		$userInfo = $this->facebook->api_client->users_getInfo( $this->fbUserId, 'locale' );
		$this->view->language = substr($userInfo[0]['locale'], 0, 2);
	}
	
	public function involveAction() {
		
	}
    
}