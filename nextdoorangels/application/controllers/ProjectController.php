<?php 
require_once 'FacebookController.php';

class ProjectController extends FacebookController {

    private function lookupAdress($address) {
        $httpClient = new Zend_Http_Client("http://maps.google.com/maps/geo");
        $httpClient->setParameterGet("q", urlencode($address))->setParameterGet("key", "ABQIAAAAquIIHMFUJg94ExRueMgLfBRqIoZm6jji5rsO5B8qBiDUbrl1FRQdaeL1jsj3fTRyvOT7EK7euL9jmA")->setParameterGet("sensor", "false")->setParameterGet("output", "json");
        $result = $httpClient->request("GET");
        $response = Zend_Json_Decoder::decode($result->getBody(), Zend_Json::TYPE_OBJECT);
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
            $place = $request->getParam('place');
            $deadline_day = $request->getParam('deadline_day');
            $deadline_month = $request->getParam('deadline_month');
            $deadline = mktime(23, 59, 59, $deadline_month, $deadline_day);
            if ($deadline == FALSE) {
                $this->view->errors['Deadline'] = 'is not a valid date.';
            }
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
            $placeValidator = new Zend_Validate_StringLength(3);
            if (!$placeValidator->isValid($place)) {
                $this->view->errors['Place'] = current($placeValidator->getMessages());
            }
            // do the commit
            if (count($this->view->errors) == 0) {
                // do geo lookup
                list($lng, $lat) = $this->lookupAdress($place);
                // commit project
				$event_data = array('name'=>$title, 'city'=>$place, 'start_time'=>time(), 'end_time'=>$deadline,
				'category' => 1, 'subcategory' => 1, 'location' => 'Your House', 'host' => 'You');
                try {
                    $event_id = $this->facebook->api_client->events_create($event_data);
	                $table = new Model_DbTable_Problems();
	                $table->insert(array('p_name'=>$title, 'p_description'=>$description, 'p_address'=>$place, 'p_lat'=>$lat, 'p_lng'=>$lng, 'fb_user_id'=>$this->fbUserId, 'p_deadline'=>date("Y-m-d H:i:s", $deadline), 'p_created_at'=>date("Y-m-d H:i:s", time()), 'fb_event_id'=>$event_id));
	                // inform user & forward to index
	                $this->_helper->FlashMessenger('You successfully created the social project <fb:eventlink eid="'.$event_id.'"/>. Just click on the link and invite some friends. We wish you a lot of success. ');
                }
                catch(Exception $e) {
	                $this->_helper->FlashMessenger(array('error'=>'There has been an error creating your social project. Please try again later.'));
					Zend_Registry::get('logger')->err($e->getMessage()); 
                } 
                return $this->_forward('index', 'index');
            }
        }
    }
    
    public function showAction() {
        $this->_helper->Layout->disableLayout();
        $this->_helper->ViewRenderer->setNoRender();
        $output = $_GET['callback'].'([';
        $locations = array();
        $table = new Model_DbTable_Problems();
        $rows = $table->fetchAll();
        $count = count($rows);
        $i = 0;
        foreach ($rows as $row) {
            $output .= '{"templates":["{root}/templates/fb.html"],"icon":"slp",';
            $output .= '"place":"'.$row['p_address'].'",';
            $output .= '"projectTitle":"'.$row['p_name'].'",';
            $output .= '"description":"'.$row['p_description'].'",';
            $output .= '"userId":"'.$row['fb_user_id'].'",';
            $output .= '"joinURL":"http://www.facebook.com/event.php?eid='.$row['fb_event_id'].'",';
            $output .= '"lat":'.$row['p_lat'].',';
            $output .= '"lng":'.$row['p_lng'];
            $output .= '}';
            $i++;
            if ($i != $count) {
                $output .= ',';
            }
        }
        $output .= ']);';
        $this->_response->setHeader('Content-Type', 'text/plain')->setBody($output);
    }
}

