<?php 
require_once 'FacebookController.php';

class ProjectController extends FacebookController {

    private function lookupAdress($street, $city) {
        if (strlen(trim($street)) > 0) {
            $address = $street.', '.$city;
        } else {
            $address = $city;
        }
        //Zend_Registry::get('logger')->debug("lookup adress: ".$address);
        $httpClient = new Zend_Http_Client("http://maps.google.com/maps/geo");
        $httpClient->setParameterGet("q", $address)->setParameterGet("key", "ABQIAAAAquIIHMFUJg94ExRueMgLfBRqIoZm6jji5rsO5B8qBiDUbrl1FRQdaeL1jsj3fTRyvOT7EK7euL9jmA")->setParameterGet("sensor", "false")->setParameterGet("output", "json");
        $result = $httpClient->request("GET");
        $response = Zend_Json_Decoder::decode($result->getBody(), Zend_Json::TYPE_OBJECT);
        //Zend_Registry::get('logger')->debug("response: ".print_r($response, true));
        return array($response->Placemark[0]->Point->coordinates[0], $response->Placemark[0]->Point->coordinates[1]);
    }
    
    public function addAction() {
        $this->requireLogin();
        $this->view->errors = array();
        $request = $this->getRequest();
        $doit = $request->getParam('do-it');
        // check permission
        $this->view->permission = $this->facebook->api_client->users_hasAppPermission('create_event');
        if (isset($doit) && $this->view->permission) {
            // get values
            $title = $request->getParam('title');
            $description = $request->getParam('description');
            $street = $request->getParam('street');
            $city = $request->getParam('city');
            $this->view->params = $request->getParams();
            date_default_timezone_set('GMT');
            $createdTime = new Zend_Date();
            $startTime = new Zend_Date();
            $startTime->set($request->getParam('date_month'), Zend_Date::MONTH);
            $startTime->set($request->getParam('date_day'), Zend_Date::DAY);
            $startTime->set($request->getParam('time_hour'), Zend_Date::HOUR_SHORT_AM);
            $startTime->set($request->getParam('time_min'), Zend_Date::MINUTE);
            if ($request->getParam('time_ampm') == 'pm') {
                $startTime->add(12, Zend_Date::HOUR);
            }
            $endTime = clone $startTime;
            $endTime->add(4, Zend_Date::HOUR);
			$fbTime = clone $startTime;
            $fbTime->setTimezone('America/Los_Angeles');
            $this->view->date = $startTime->getTimestamp() + $fbTime->getGmtOffset();
            // validate values
            $titleValidator = new Zend_Validate();
            $titleValidator->addValidator( new Zend_Validate_StringLength(8));
            if (!$titleValidator->isValid($title)) {
                $this->view->errors['Title'] = current($titleValidator->getMessages());
            }
            $descriptionValidator = new Zend_Validate_StringLength(8);
            if (!$descriptionValidator->isValid($description)) {
                $this->view->errors['Description'] = current($descriptionValidator->getMessages());
            }
            $streetValidator = new Zend_Validate_StringLength(5);
            if (!$streetValidator->isValid($street)) {
                $this->view->errors['Street'] = current($streetValidator->getMessages());
            }
            $cityValidator = new Zend_Validate_StringLength(3);
            if (!$cityValidator->isValid($city)) {
                $this->view->errors['City'] = current($cityValidator->getMessages());
            }
            // do geo lookup & validate address
            list($lng, $lat) = $this->lookupAdress($street, $city);
            if (!(isset($lng) && isset($lat))) {
                $this->_helper->FlashMessenger(array('error'=>'The address can not be found. Please check street and city.'));
            } else {
                // do the commit
                if (count($this->view->errors) == 0) {
                    try {
                        // commit project
                        $event_data = array('name'=>$title, 'city'=>$city, 'location'=>$street, 'start_time'=>($startTime->getTimestamp() + $fbTime->getGmtOffset()), 'end_time'=>($endTime->getTimestamp() + $fbTime->getGmtOffset()), 'category'=>2, 'subcategory'=>30, 'host'=>'You');
                        $event_id = $this->facebook->api_client->events_create($event_data);
                        $table = new Model_DbTable_Problems();
                        $table->insert(array('p_name'=>$title, 'p_description'=>$description, 'p_city'=>$city, 'p_location'=>$street, 'p_lat'=>$lat, 'p_lng'=>$lng, 'fb_user_id'=>$this->fbUserId, 'p_deadline'=>$startTime->toString('YYYY-MM-dd HH:mm:ss'), 'p_created_at'=>$createdTime->toString('YYYY-MM-dd HH:mm:ss'), 'fb_event_id'=>$event_id));
                        // inform user & forward to index
                        $this->_helper->FlashMessenger('You successfully created the social project <fb:eventlink eid="'.$event_id.'"/>. Just click on the link and invite some friends. We wish you a lot of success. ');
                    }
                    catch(Exception $e) {
                        if ($e->getMessage() == 'Unknown city') {
                            $this->view->errors['City'] = ': '.$city.' is unknown in Facebook. Please try a different name.';
                            return;
                        } elseif (strpos($e->getMessage(), 'Integrity constraint violation') !== FALSE) {
                            $this->_helper->FlashMessenger(array('error'=>'You tried to create a project at '.$street.', '.$city.' - the same location of an existing project. Probably you just reloaded the page, and everything is fine. If not, please create a project at another location.'));
                        } else {
                            $this->_helper->FlashMessenger(array('error'=>'There has been an error creating your social project. Please try again later.'));
                            Zend_Registry::get('logger')->err($e->getMessage());
                        }
                    }
                    return $this->_forward('index', 'index');
                }
            }
        }
    }
    
    public function listAction() {
        $this->_helper->Layout->disableLayout();
        $this->_helper->ViewRenderer->setNoRender();
        $table = new Model_DbTable_Problems();
        $rows = $table->fetchAll();
        $values = array();
        foreach ($rows as $row) {
            $value = array("templates"=>array("{root}/templates/fb.html"), "icon"=>"slp", "city"=>htmlspecialchars($row['p_city']), "location"=>htmlspecialchars($row['p_location']), "address"=>htmlspecialchars($row['p_location'].', '.$row['p_city']), "projectTitle"=>htmlspecialchars($row['p_name']), "description"=>nl2br(htmlspecialchars($row['p_description'])), "userId"=>$row['fb_user_id'], "joinURL"=>"javascript:window.top.location = 'http://www.facebook.com/event.php?eid=".$row['fb_event_id']."';", "lat"=>$row['p_lat'], "lng"=>$row['p_lng']);
            array_push($values, $value);
        }
        $output = $_GET['callback'].'('.Zend_Json::encode($values).');';
        $this->_response->setHeader('Content-Type', 'text/plain')->setBody($output);
    }
}

