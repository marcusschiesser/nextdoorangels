<?php 
$err = error_reporting(E_ERROR);
require_once 'facebook/facebook.php';
error_reporting($err);
class FacebookController extends Zend_Controller_Action {
    private $simulateFb;
    private $canvasUrl;
    public $fbUserId = "1234567";
    /**
     * Facebook api
     * @var Facebook
     */
    protected $facebook;
	
    public function init() {
    	$allConfig = $this->getInvokeArg('bootstrap')->getOptions();
		$config = $allConfig['facebook'];
		$this->canvasUrl = $config['canvasUrl'];
		$this->simulateFb = $config['simulateFb'] ? $config['simulateFb'] : false;
        if ($this->simulateFb) {
            Zend_Session::start();
            parent::init();
        } else {
			$apiSecret = $config['apiSecret'] ? $config['apiSecret'] : getenv('FB_APISECRET');
            $this->facebook = new Facebook($config['apiKey'], $apiSecret);
            $session_key = md5($this->facebook->api_client->session_key);
            if (!Zend_Session::isStarted()) {
                Zend_Session::setId($session_key);
                Zend_Session::start();
            }
            parent::init();
        }
    }
    protected function requireLogin() {
        if (!$this->simulateFb) {
            $this->fbUserId = $this->facebook->require_login();
        }
    }
    protected function _redirect($url, array $options = array()) {
        if (!$this->simulateFb) {
            $this->facebook->redirect($this->canvasUrl.$url);
        } else {
            parent::_redirect($url, $options);
        }
    }
}
?>
