<?php 
require_once 'FacebookController.php';

class IndexController extends FacebookController {

    function indexAction() {
    	$this->requireLogin();
    }
    
}

