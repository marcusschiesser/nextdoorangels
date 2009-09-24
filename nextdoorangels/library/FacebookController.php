<?php 
$err = error_reporting(E_ERROR);
require_once 'facebook/facebook.php';
error_reporting($err);
class FacebookController extends Zend_Controller_Action {
    public $simulateFb = false;
    public $useTestApplication = false;
    public $apiKey = "39ba65eb321178034ce6abf4055fe99f";
    public $apiSecret = "60f2f411e3b124404dbbf81787198690";
    public $canvasUrl = "http://apps.facebook.com/nextdoorangels";
    public $testApiKey = '28c...b05';
    public $testApiSecret = 'df6...3fb5';
    public $testCanvasUrl = "http://apps.facebook.com/myfbapptest";
    public $fbUserId = "1234567";
    /**
     * Facebook api
     * @var Facebook
     */
    protected $facebook;
    public function init() {
        if ($this->simulateFb) {
            Zend_Session::start();
            parent::init();
        } else {
            if ($this->useTestApplication) {
                $this->apiKey = $this->testApiKey;
                $this->apiSecret = $this->testApiSecret;
                $this->canvasUrl = $this->testCanvasUrl;
            }
            $this->facebook = new Facebook($this->apiKey, $this->apiSecret);
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
header('Content-Type: text/html; charset=UTF-8');
?>
