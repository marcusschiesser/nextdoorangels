<?php 
$err = error_reporting(E_ERROR);
require_once 'facebook/facebook.php';
error_reporting($err);

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    
    public $apiKey = "39ba65eb321178034ce6abf4055fe99f";
    public $apiSecret = "60f2f411e3b124404dbbf81787198690";
    
    function indexAction() {
        $facebook = new Facebook($this->apiKey, $this->apiSecret);
        $facebook->require_login();
        
        $this->view->title = 'Testing a FB App';
    }

    
}

