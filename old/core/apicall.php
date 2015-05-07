<?php

     include_once('set_error_log.php');

     $controllerList = array(
                         'event'=>'event.php',
                         'user'=>'user.php',
                         'location'=>'location.php',
                         'request'=>'request_user_location.php',
                         'group'=>'user_group.php',
                         'share'=>'share_user_location.php',
                         'save'=>'save_user_location.php',
                         'trackduration'=>'track_user_location',
                         'trackuser'=>'track_user_location.php'
                    );
          
     $actionList = array(
                    'event'   =>array(
                                   'get'=>'1',
                                   'push'=>'11'
                              ),
                    'user'    =>array(
                                   'message_has_reached'=>'4',
                                   'push'=>'8',
                                   'get'=>'2',
                                   'update'=>'7',
                                   'sendVerificationCode'=>'1',
                                   'updateUserStatus'=>'2',
                                   'updateRegStatus'=>'2',
                                   'updateExpiryDate'=>'2',
                                   'updateAllowLastSeen'=>'2',
                                   'updateNumberOfShare'=>'2',
                                   'updateProfilePic'=>'3',
                                   'updateNickName'=>'2',
                                   'getExpiryDate'=>'1',
                                   'getUserArrayByMobileNumbers'=>'3',
                                   'getUserArrayByMobileNumbersTrial'=>'3',
                                   'gettry'=>'1',
                                   'flushtable'=>'1',
                                   'print_image'=>'1',
                                   'register_test_user'=>'3',
                                   'register_counter'=>'1',
                                   'register_flush'=>'0',
                                   'show_table'=>'1',
                                   'get_test_user'=>'0',
                                   'get_friend_detail'=>'3',
                                   'user_statistics'=>'3',
                                   'send_notification_to_all'=>'3',
                                   'updateAutoLocateList'=>'3',
                                   'addAutoLocateUser'=>'3',
                                   'removeAutoLocateUser'=>'3',
                                   'no_of_users'=>'0'
                                   
                              ),
                    'location'=>array(
                                   'push'=>'5',
                                   'little_fluffy'=>'5',
                                   'update'=>'7',
                                   'updateShareCount'=>'2',
                                   'updateLatitudeLongitude'=>'3',
                                   'updateActualAddress'=>'2',
                                   'updateNote'=>'2',
                                   'updateGeoFence'=>'2',
                                   'gettry'=>'1',
                                   'get'=>'1'
                              ),
                    'request'=>array(
                                   'push'=>'8',
                                   'get'=>'1',
                                   'getIndividualList'=>'1',
                                   'getGroupList'=>'1',
                                   'approveRequest'=>'13',
                                   'rejectRequest'=>'2',
                                   'getSentRequestIndividualList'=>'1',
                                   'getSentRequestGroupList'=>'1',
                                   'getReceiveRequestIndividualList'=>'1',
                                   'getReceiveRequestGroupList'=>'1',
                                   'gettry'=>'1'
                              ),
                    'group'   =>array(
                                   'push'=>'5',
                                   'get'=>'3',
                                   'remove_member'=>'4',
                                   'add_member'=>'4',
                                   'get_all_shared_location'=>'3',
                                   'approve_request'=>'9',
                                   'request_location'=>'6',
                                   'share_location'=>'12',
                                   'update'=>'5',
                                   'get_group_image'=>'3',
                                   'delete'=>'3',
                                   'leave_group'=>'3',
                                   'getGroupList'=>'1'
                              ),
                    'share'   =>array(
                                   'push'=>'15',
                                   'updateReadStatus'=>'2',
                                   'getSentShareIndividualList'=>'1',
                                   'getSentShareGroupList'=>'1',
                                   'getReceiveShareIndividualList'=>'1',
                                   'getReceiveShareGroupList'=>'1',
                                   'acknowledge_notification'=>'4',
                                   'get'=>'4',
                                   'gettry'=>'1'
                              ),
                    'save'    =>array(
                                   'push'=>'10',
                                   'update_saved_location'=>'11',
                                   'gettry'=>'1',
                                   'getUserSavedLocation'=>'1',
                                   'updateNote'=>'3',
                                   'updateTagName'=>'3',
                                   'flushtable'=>'1',
                                   'getSaveLocationImage'=>'3',
                                   'deleteSavedLocation'=>'3'
                              ),
                    'trackduration'=>array(
                                  'push'=>'6'
                              ),
                    'tracklocation'=>array(
                                   'push'=>'6'
                              )
                   );
          
     if($_SERVER["REQUEST_METHOD"]=="POST")
     {
          
          $controller    =    "";
          $action        =    "";
          $argumentCount =    "";
          $controller1   =    "";
          $requestJson   =    json_encode(array());
          $dataJson      =    "";
          $responseJson  =    array();
          try{
               if(isset($_POST['controller'],$_POST['action'],$_POST['data'])){
                    $controller    =    $_POST['controller'];
                    $action        =    $_POST['action'];
                    $dataArray     =    json_decode(stripslashes($_POST['data']),true);
                    
                    if(array_key_exists($controller,$controllerList))
                    {
                         $controller1 = ($controllerList[$controller]);
                    }else{
                         requestFail("Invalid Request. Controller not found");
                    }
                    if(array_key_exists($action,$actionList[$controller]))
                    {
                         $argumentCount = ($actionList[$controller][$action]);
                    }else{
                         requestFail("Invalid Request. Action in controller not found");
                    }
                    
                    if($argumentCount==count($dataArray))
                    {
                         $dataArr = array();
                         foreach($dataArray as $key=>$val){
                              $dataArr[$key] = $val;
                         }
                         include_once ($controller1);               // including the Class File
                         
                         $object = "";
                              
                         switch($controller){
                              case "event":
                                   $object = new Event();
                                   break;
                              case "user":
                                   $object = new User();
                                   break;
                              case "location":
                                   $object = new Location();
                                   break;
                              case "request":
                                   $object = new RequestUserLocation();
                                   break;
                              case "group":
                                   $object = new UserGroup();
                                   break;
                              case "share":
                                   $object = new ShareUserLocation();
                                   break;
                              case "save":
                                   $object = new SaveUserLocation();
                                   break;
                              case "trackduration":
                                   $object = new TrackUserDuration();
                                   break;
                              case "tracklocation":
                                   $object = new TrackUserLocation();
                                   break;
                         }
                         
                         $returnValue = "";
                         
                         $returnValue = $object->$action($dataArr);
                         header("Access-Control-Allow-Origin: *");
                         header('Cache-Control: no-cache, must-revalidate');
                         header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                         // headers to tell that result is JSON
                         header('Content-type: application/json');
                         
                         print_r(json_encode((array)$returnValue));
                         exit;
                         
                    }else{
                         requestFail("Invalid Request. Number of parameters doesn't match");
                    }
               }else{
                    requestFail("Invalid Request. Parameter name doesn't match");
               }
          }catch(Exception $e){
               requestFail("Invalid Request. Check your parameters.");
          }
     }else{
          requestFail("Invalid Request. Request is accepted only by POST method.");
     }
     function requestFail($msg){
          header('Cache-Control: no-cache, must-revalidate');
          header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
          // headers to tell that result is JSON
          header('Content-type: application/json');
          $responseArray = array();
          $responseArray['respStatus'] = 400;
          $responseArray['respMsg']    = $msg;
          print_r(json_encode($responseArray));
          exit;
     }
?>
