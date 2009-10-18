<?php 
require_once 'FacebookController.php';

class IndexController extends FacebookController {

    function indexAction() {
        $this->requireLogin();
        $allConfig = $this->getConfig();
        $this->view->mapRoot = $allConfig['map']['root'];
        
        $this->view->userId = $this->fbUserId;
        $userInfo = $this->facebook->api_client->users_getInfo($this->fbUserId, 'locale');
        $this->view->language = substr($userInfo[0]['locale'], 0, 2);
    }
    
    function inviteAction() {
        $this->requireLogin();

        $fql = 'SELECT uid FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1='.$this->fbUserId.') AND is_app_user = 1';
        $_friends = $this->facebook->api_client->fql_query($fql);
        // Extract the user ID's returned in the FQL request into a new array.
        $friends = array();
        if (is_array($_friends) && count($_friends)) {
            foreach ($_friends as $friend) {
                $friends[] = $friend['uid'];
            }
        }
        // Convert the array of friends into a comma-delimeted string.
        $this->view->friends = implode(',', $friends);
        // Prepare the invitation text that all invited users will receive.
        $this->view->content = "<fb:name uid=\"".$this->fbUserId."\" firstnameonly=\"true\" shownetwork=\"false\"/> has started using <a href=\"".$this->view->canvasUrl."/\">NextdoorAngels</a> and thought it's so cool even you should try it out!\n"."<fb:req-choice url=\"".$this->view->canvasUrl."\" label=\"Put NextdoorAngels on your profile\"/>";
    }
    
    function doinviteAction() {
        $this->requireLogin();
        
        $request = $this->getRequest();
        $ids = $request->getParam('ids');
        
        if (isset($ids)) {
            $this->_helper->FlashMessenger("Thank you for inviting ".sizeof($ids)." of your friends to become NextdoorAngels.");
        }
        
        return $this->_forward('index', 'index');
    }
}

