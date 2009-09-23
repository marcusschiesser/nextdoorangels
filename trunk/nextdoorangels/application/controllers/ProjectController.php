<?php 
$err = error_reporting(E_ERROR);
require_once 'facebook/facebook.php';
error_reporting($err);

class ProjectController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }
    
    private function lookupAdress($address) {
        $httpClient = new Zend_Http_Client("http://maps.google.com/maps/geo");
        $httpClient->setParameterGet("q", urlencode($address))->setParameterGet("key", "ABQIAAAAquIIHMFUJg94ExRueMgLfBRqIoZm6jji5rsO5B8qBiDUbrl1FRQdaeL1jsj3fTRyvOT7EK7euL9jmA")->setParameterGet("sensor", "false")->setParameterGet("output", "json");
        $result = $httpClient->request("GET");
        $response = Zend_Json_Decoder::decode($result->getBody(), Zend_Json::TYPE_OBJECT);
        return array($response->Placemark[0]->Point->coordinates[0], $response->Placemark[0]->Point->coordinates[1]);
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
                // do geo lookup
                list($lng, $lat) = $this->lookupAdress($place);
                // commit project
                $table = new Model_DbTable_Problems();
                $table->insert(array('p_name'=>$title, 'p_description'=>$description, 'p_address' => $place, 'p_lat' => $lat, 'p_lng' => $lng));
                // inform user & forward to index
                $this->_helper->FlashMessenger('You successfully created a social project. We wish you a lot of success.');
                return $this->_forward('index', 'index');
            }
        }
    }
    
}

