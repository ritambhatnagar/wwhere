<?php

    include_once('set_error_log.php');
    include_once "configuration.php";
    include_once ("error_message.php");
    include_once "gcm_server_php/GCM.php";
    
    $connectDb = new Configuration();
    $gcm = new GCM();
    $connectDb->dbConnect();
    
    class User
    {
        public function register_flush(){
            $query          =   "Truncate `test_user`";
            
            mysql_query($query);
        }
        public function register_counter($arr){
            $query          =   "SELECT COUNT(*) as `count` FROM `test_user`";
                
            $resp = mysql_fetch_assoc(mysql_query($query));
            
            $counter = $resp["count"];
            
            $respArr = array();
                
            $respArr['respStatus']      = 200;
            $respArr['respMsg']         = $counter;
        
            return $respArr;

        }
        public function show_table($arr){
            $table_name = $arr[0];
            $query          =   "Describe $table_name";
            $array = array();
            $rs = mysql_query($query);
            while($resp = mysql_fetch_assoc($rs)){
                $array[] = $resp;
            }
            
            $respArr = array();
                
            $respArr['respStatus']      = 200;
            $respArr['respMsg']         = $array;
            
            return $respArr;
        }
        
        public function send_notification_to_all($arr){
            global $gcm;
            $os      =  $arr[0];
            $header  =  $arr[1];
            $message =  $arr[2];
            
            $query3 =  "SELECT  `dcm_code`,`reg_status`
                                From `user`
                                WHERE `reg_status`=1 and `device_type_id`=".$os;
                
            $resultSet3 = mysql_query($query3);
                
            $receiver_dcm_keys = array();
                
            if(mysql_numrows($resultSet3)>0){
                while ($records = mysql_fetch_assoc($resultSet3)){
                    $receiver_dcm_keys[]   =   $records['dcm_code'];
                }
                $gcm_notification = array();
                // gcm_type=5
                $gcm_notification['GCM_NOTI_TYPE']  =   5;
                $gcm_notification['GCM_DATA_PACKET']=   array("header"=>$header,
                                                              "message"=>$message
                                                            );
                $gcm->send_notification($receiver_dcm_keys,$gcm_notification);
                    
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = "Notification sent to all";
            }else{
                
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "No registered user";
            }
            return $respArr;
        }
        public function send_email_to_all($arr){
            
        }
        public function register_test_user($arr){
            $email_id       =   $arr[0];
            $prefered_os    =   $arr[1];
            $ip_address     =   $arr[2];
            $reg_date       =   date("Y-m-d H:i:s",time());
            
            $query          =   "SELECT `reg_date` FROM `test_user` WHERE `ip_address`='".$ip_address."' ORDER BY reg_id";
            $resultSet      =   mysql_query($query);
            $numRecords     =   mysql_numrows($resultSet);
            $resultArray    =   array();
            $serve_request  =   true;
            while($resultArr   =   mysql_fetch_assoc($resultSet)){
                $resultArray[] = $resultArr;
            }
            $diff_time_sec  =   strtotime($reg_date) - strtotime($resultArray[$numRecords-1]['reg_date']);
            if(($diff_time_sec)> 300){
                if($diff_time_sec>=86400){
                    $serve_request = true;
                }else{
                    if($numRecords<100){
                        $serve_request = true;
                    }else{
                        $serve_request = false;
                    }
                }
            }else{
                if($numRecords<50){
                    $serve_request = true;
                }else{
                    $serve_request = false;
                }
            }
            
            if($serve_request){
                $query          =   "INSERT INTO `test_user`(`email_id`,`prefer_os_id`,`ip_address`,`reg_date`) values('".$email_id."','".$prefered_os."','".$ip_address."','".$reg_date."')";
                
                mysql_query($query);
                    
                $respArr = array();
                    
                if(mysql_affected_rows()>0){
                    $respArr['respStatus']      = 200;
                    $respArr['respMsg']         = "success";
                }else{
                    $respArr['respStatus']      = 400;
                    $respArr['respMsg']         = "can't be processed";
                }
            }else{
                $respArr['respStatus']  = 401;
                    $respArr['respMsg'] = "wait";
            }
            return $respArr;
        }
        public function get_test_user(){
            
            $query          =   "SELECT `email_id`,`prefer_os_id` FROM `test_user`";
                
            $recordset = mysql_query($query);
                
            $array = array();
            $rs = mysql_query($query);
            while($resp = mysql_fetch_assoc($rs)){
                $array[] = $resp;
            }
            $respArr = array();
                
            $respArr['respStatus']      = 200;
            $respArr['respMsg']         = $array;
                
            return $respArr;
        }
        public function push($arr)
        {
            global $message,$gcm;
            
            $nick_name      =   $arr[0];
            $profile_pic    =   $arr[1];
            $mobile_number  =   $arr[2];
            $user_status    =   $arr[3];
            $dcm_code       =   $arr[4];
            $device_id      =   $arr[5];
            $device_type_id =   $arr[6];
            $birth_year     =   $arr[7];
            $reg_status     =   1;                  // Change this after it is on production
            $currDate       =   date ('Y-m-d H:i:s');
            $last_seen_on   =   $date_of_joining = $currDate;
            
            $check_array    =   array();
            
            $check_array    =   $this->check_user_exist($mobile_number);
            
            if($mobile_number=="" || $device_id=="" || $dcm_code=="" ){
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "Mobile number, device_id and GCM Code is required. ";
                return $respArr;
            }
            $respArr = array();
            if($check_array['count']==0){
                
                $date_of_expiry = date('Y-m-d H:i:s',strtotime($currDate . " +365Days"));
                $query          = "INSERT INTO `user`(`nick_name`,`profile_pic`,`mobile_number`,`last_seen_on`,`date_of_joining`,`date_of_expiry`,`user_status`,`reg_status`,`dcm_code`,`device_id`,`device_type_id`,`birth_year`)
                                      values('".$nick_name."','".$profile_pic."','".$mobile_number."','".$last_seen_on."','".$date_of_joining."','".$date_of_expiry."','".$user_status."','" .$reg_status. "','" .$dcm_code. "','" .$device_id. "','" .$device_type_id. "','" .$birth_year. "')";
                
                mysql_query($query);
                    
                if(mysql_affected_rows()>0){
                    $respArr['respStatus']      = 200;
                    $respArr['respMsg']         = $message["user"]["regSucc"];
                    $respArr['userID']          = mysql_insert_id();
                }else{
                    $respArr['respStatus']      = 400;
                    $respArr['respMsg']         = $message["user"]["regFail"];
                    $respArr['userID']          = "";
                }
            }else{
                $query2 =  "UPDATE `user`
                            SET `nick_name`='".$nick_name."',`profile_pic`='".$profile_pic."',`last_seen_on`='".$last_seen_on."',`dcm_code`='".$dcm_code."',`device_id`='".$device_id."',`device_type_id`='".$device_type_id."',`birth_year`='".$birth_year."',
                                `allow_last_seen`='1',`user_status`='".$user_status."',`reg_status`='0',`mobile_number`='".$mobile_number."'
                            Where `user_id`='".$check_array['user_id']."'";
                    
                mysql_query($query2);
                    
                if($check_array['reg_status']=="5"){
                     
                    // select all friends to notify them that user in now on wWhere
                    $friend_list = rtrim($check_array['friend_user_list'],",");
                    $query3 =  "SELECT `dcm_code`,`device_type_id`
                                From `user`
                                WHERE `reg_status`=1 and `user_id` in (".$friend_list.")";
                        
                    $resultSet3 = mysql_query($query3);
                        
                    $receiver_dcm_key = array();
                        
                    if(mysql_numrows($resultSet3)>0){
                        
                        while ($records = mysql_fetch_assoc($resultSet3)){
                            $receiver_dcm_key = $records['dcm_code'];
                        }
                        
                        $gcm_notification = array();
                        $gcm_notification['GCM_NOTI_TYPE'] = 3;
                        $gcm_notification['GCM_DATA_PACKET']= array("friend_user_id"=>$check_array['user_id'],
                                                                    "mobile_number"=>$mobile_number,
                                                                    "nick_name"=>$nick_name,
                                                                    "user_status"=>$user_status
                                                                    );
                        //print_r($gcm_notification);
                        //print_r($receiver_dcm_key);
                        $gcm->send_notification($receiver_dcm_key,$gcm_notification);
                            
                    }    
                    $respArr['respStatus']  = 200;
                    $respArr['respMsg']     = $message["user"]["regSucc"];
                }
                else{
                    $respArr['respMsg']     = $message["user"]["regUserExist"];
                    
                    include_once('save_user_location.php');
                    $object = new SaveUserLocation();
                    $respArr['savedLocations'] = $object->getUserSavedLocation($check_array['user_id']);
                    include_once('user_group.php');
                    $object = new UserGroup();
                    $respArr['groups'] = $object->getGrpList($check_array['user_id']);
                    $respArr['autoLocateList'] = $this->get_autolctUsrList($check_array['user_id']);
                }
                $respArr['userID']          = $check_array['user_id'];
            }
            return $respArr;
        }
        
        protected function get_autolocateUserList($user_id){
            $query       =  "Select autolocate_user_list From user WHERE user_id = $user_id";
            
            $list = mysql_fetch_assoc(mysql_query($query));
            
            $autolocate_user = "";
            
            $resultArray = array();
            
            if(isset($list['autolocate_user_list']) && $list['autolocate_user_list']!="" && !is_null($list['autolocate_user_list'])){
                //$autolocate_user = $list['autolocate_user_list'];
                $autolocate = $list['autolocate_user_list'];
                $query      =  "SELECT user_id, mobile_number, nick_name
                                FROM   user
                                WHERE  user_id in ($autolocate)";
                    
                $resultList = mysql_query($query);
                
                while($row = mysql_fetch_assoc($resultList)){
                    $resultArray[] = $row;
                }
                
            }
            
            return $resultArray ;
        }
        public function addAutoLocateUser($arr){
            global $connectDb;
            
            $user_id    =  $arr[0];
            $device_id  =  $arr[1];
            $autolocate_user_id  =  trim($arr[2]);
            
            $respArr = array();
            if($connectDb->verify_user($user_id,$device_id)!=0){
                $query  = "SELECT count(*) as number,autolocate_user_list FROM user Where user_id = $user_id and concat(',',autolocate_user_list,',') like '%,".$autolocate_user_id.",%'";
                
                $number = mysql_fetch_assoc(mysql_query($query));
                
                if($number['number']>0){
                    $respArr['respStatus']  = 401;
                    $respArr['respMsg']     = "already exist";
                }else{
                    $update_criteria = "CONCAT( autolocate_user_list, ',".$autolocate_user_id."')";
                    if($number['autolocate_user_list']==""){
                        $update_criteria = $autolocate_user_id;
                    }
                    $query  = "UPDATE user set autolocate_user_list = ".$update_criteria ." WHERE user_id = $user_id";
                    
                    if(mysql_query($query)){
                        
                        $respArr['respStatus']  = 200;
                        $respArr['respMsg']     = "user autolocated on wWhere";
                        
                    }else{
                        $respArr['respStatus']  = 400;
                    }
                }
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "You are unauthorized for sending this request";
                
            }
            return $respArr;
        }
        public function removeAutoLocateUser($arr){
            global $connectDb;
            
            $user_id    =  $arr[0];
            $device_id  =  $arr[1];
            $autolocate_user_id  =  trim($arr[2]);
            
            $respArr = array();
            if($connectDb->verify_user($user_id,$device_id)!=0){
                $query  = "SELECT count(*) as number,autolocate_user_list FROM user Where user_id = $user_id and concat(',',autolocate_user_list,',') like '%,".$autolocate_user_id.",%'";
                
                $number = mysql_fetch_assoc(mysql_query($query));
                
                if($number['number']==0){
                    $respArr['respStatus']  = 401;
                    $respArr['respMsg']     = "user does not exist";
                }else{
                    //echo $number['autolocate_user_list'];
                    $mainstring = ','.$number['autolocate_user_list'].',';
                    $findstr = ','.$autolocate_user_id.',';
                    $replacewith = ',';
                    $new_list = str_replace($findstr,$replacewith,$mainstring);
                    $new_list = substr($new_list,1,strlen($new_list)-2);
                    $query  = "UPDATE user set autolocate_user_list ='". $new_list."' WHERE user_id = $user_id";
                    //echo $query;
                    if(mysql_query($query)){
                        
                        $respArr['respStatus']  = 200;
                        $respArr['respMsg']     = "user removed from autolocated list";
                        
                    }else{
                        $respArr['respStatus']  = 400;
                        $respArr['respMsg']     = "user not removed from autolocated list. Try again";
                    }
                }
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "You are unauthorized for sending this request";
                
            }
            return $respArr;
        }
        
        public function get_friend_detail($arr){
            global $connectDb;
            $user_id    =  $arr[0];
            $device_id  =  $arr[1];
            $friend_id  =  $arr[2];
            
            if($connectDb->verify_user($user_id,$device_id)!=0){
                $query = "SELECT `nick_name`,`mobile_number`,`profile_pic`,`last_seen_on`,`allow_last_seen`,`date_of_joining`,`user_status`,`reg_status`,`friend_user_list`
                          From `user`
                          WHERE `user_id`='".$friend_id."'";
                    
                $recordset = mysql_query($query);
                    
                $user_record = array();
                    
                if(mysql_numrows($recordset)>0){
                    $user_record            = mysql_fetch_assoc($recordset);
                    
                    $respArr['respStatus']  = 200;
                    $respArr['respMsg']     = "Got your friend on wWhere";
                    $respArr['userDetail']  = $user_record;
                    
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "Friend not on wWhere. Why not invite him?";
                    $respArr['userDetail']  = array();
                }
                    
                return $respArr;
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "You are unauthorized for sending this request";
                return $respArr;
            }
        }
        public function user_statistics($arr){
            
            global $connectDb;
            $user_id        =   $arr[0];
            $device_id      =   $arr[1];
            $full_status    =   $arr[2];
            
            if($limit!=0 && $limit!=""){
                $limitClause  = " LIMIT $limit";
            }
            if($connectDb->verify_user($user_id,$device_id)!=0){
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = "Your history on wWhere";
                
                // Save Location Stats
                $savedLocationQuery = "SELECT `saved_location`.*,`location`.`latitude`,`location`.`longitude`
                                       FROM `saved_location`,`location`
                                       WHERE `location`.`location_id`=`saved_location`.`location_id` and `user_id`='".$user_id."'
                                       ORDER BY saved_id desc";
                $resultSet1 = mysql_query($savedLocationQuery);
                    
                $respArr['savedLocationsCounter'] = mysql_numrows($resultSet1);
                    
                //Share Location Stats
                $sharedLocationQuery = "SELECT `share_user_location`.*, `location`.`latitude`,`location`.`longitude`
                                        FROM `share_user_location`,`location`
                                        WHERE `share_user_location`.`location_id`=`location`.`location_id` and `sender_user_id`='".$user_id."'
                                        ORDER BY `share_id` desc";
                    
                $resultSet2 = mysql_query($sharedLocationQuery);
                    
                $respArr['sharedLocationsCounter'] = mysql_numrows($resultSet2);
                    
                //Received Location Stats
                $receivedLocationQuery = " SELECT `share_user_location`.*, `location`.`latitude`,`location`.`longitude`
                                        FROM `share_user_location`,`location`
                                        WHERE `share_user_location`.`location_id`=`location`.`location_id` and `receiver_user_id`='".$user_id."'
                                        ORDER BY `share_id` desc";
                    
                $resultSet3 = mysql_query($receivedLocationQuery);
                    
                $respArr['receivedLocationsCounter'] = mysql_numrows($resultSet3);
                    
                //AutoSharedLocation Stats
                $autoSharedLocationQuery = "SELECT `request_user_location`.*, `location`.`latitude`,`location`.`longitude`,
                                                   `share_user_location`.`is_group`,`share_user_location`.`group_id`,`share_user_location`.`capture_map`,
                                                   `share_user_location`.`note`,`share_user_location`.`msg_id`,`share_user_location`.`msg_type`,
                                                   `share_user_location`.`temp_code`
                                            FROM   `share_user_location`,`location`,`request_user_location`
                                            WHERE  `share_user_location`.`reference_id`=`request_user_location`.`request_id`
                                                   and `share_user_location`.`location_id`=`location`.`location_id`
                                                   and `request_user_location`.`sender_id`='".$user_id."' and `request_user_location`.`is_approved`=1
                                                   and `request_user_location`.`is_autolocated`='1'
                                            ORDER BY `request_id` desc";
                    
                $resultSet4 = mysql_query($autoSharedLocationQuery);
                    
                $respArr['autoSharedLocationsCounter'] = mysql_numrows($resultSet4);
                    
                //AutoReceivedLocation Stats
                $atuoReceivedLocationQuery="SELECT `request_user_location`.*, `location`.`latitude`,`location`.`longitude`,
                                                   `share_user_location`.`is_group`,`share_user_location`.`group_id`,`share_user_location`.`capture_map`,
                                                   `share_user_location`.`note`,`share_user_location`.`msg_id`,`share_user_location`.`msg_type`,
                                                   `share_user_location`.`temp_code`
                                            FROM   `share_user_location`,`location`,`request_user_location`
                                            WHERE  `share_user_location`.`reference_id`=`request_user_location`.`request_id`
                                                   and `share_user_location`.`location_id`=`location`.`location_id`
                                                   and `request_user_location`.`receiver_id`='".$user_id."' and `request_user_location`.`is_approved`=1
                                                   and `request_user_location`.`is_autolocated`='1'
                                            ORDER  BY `request_id` desc";
                    
                $resultSet5 = mysql_query($atuoReceivedLocationQuery);
                    
                $respArr['autoReceivedLocationsCounter'] = mysql_numrows($resultSet5);
                    
                if($full_status==1){
                    $tempArray1  = array();
                    while($row = mysql_fetch_assoc($resultSet1)){
                        $tempArray1[] = $row;
                    }
                    $respArr['savedLocations'] = $tempArray1;
                    
                    $tempArray2  = array();
                    while($row = mysql_fetch_assoc($resultSet2)){
                        $tempArray2[] = $row;
                    }
                    $respArr['sharedLocations'] = $tempArray2;
                    $tempArray3  = array();
                    while($row = mysql_fetch_assoc($resultSet3)){
                        $tempArray3[] = $row;
                    }
                    $respArr['receivedLocations'] = $tempArray3;
                    
                    $tempArray4 = array();
                    while($row = mysql_fetch_assoc($resultSet4)){
                        $tempArray4[] = $row;
                    }
                    $respArr['autoSharedLocations'] = $tempArray4;
                        
                    $tempArray5 = array();
                    while($row = mysql_fetch_assoc($resultSet5)){
                        $tempArray5[] = $row;
                    }
                    $respArr['autoReceivedLocations'] = $tempArray5;
                }
                return $respArr;
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "You are unauthorized for sending this request";
                return $respArr;
            }
        }
        
        public function message_has_reached($arr){
            global $connectDb,$gcm;
            $respArr = array();
            $message_id  =  $arr[0];
            $receiver_id =  $arr[1];
            $device_id   =  $arr[2];
            $sender_id   =  $arr[3];
            
            if($connectDb->verify_user($receiver_id,$device_id)!=0){
                $query       =  "UPDATE message set has_reached = '1' WHERE receiver_id = $receiver_id and message_id=$message_id and sender_id=$sender_id";
                //echo $query;
                
                if(mysql_query($query)){
                    
                    $query3 =  "SELECT `dcm_code`,`device_type_id`
                                From   `user`
                                WHERE  `reg_status`=1 and `user_id` = $sender_id";
                        
                    $resultSet3 = mysql_query($query3);
                        
                    $receiver_dcm_key = array();
                        
                    while ($records = mysql_fetch_assoc($resultSet3)){
                        $receiver_dcm_key[] = $records['dcm_code'];
                    }
                        
                    $gcm_notification = array();
                    $gcm_notification['GCM_NOTI_TYPE']    = 22;
                    $gcm_notification['GCM_DATA_PACKET']  = array("msg_id"=>$message_id);
                        
                    $gcm->send_notification($receiver_dcm_key,$gcm_notification);
                    
                    $this->message_has_notified($message_id);
                    
                    $respArr['respStatus']  = 200;
                    $respArr['respMsg']     = "Message notified";
                    $respArr['MsgId']       = $message_id;
                    return $respArr;
                    
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "Message is not authorized to you";
                }
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "You are unauthorized for sending this request";
                return $respArr;
            }
        }
        
        
        protected function message_has_notified($message_id){
            $query = "UPDATE message set has_notified = '1' WHERE message_id=$message_id";
                
            if(mysql_query($query)){
                return true;
            }else{
                return false;
            }
        }
        
        public function get($arr){
            global $message;
            
            $user_id        =   $arr[0];
            $device_id      =   $arr[1];
                
            $query = "SELECT `nick_name`,`dcm_code`,`device_id`,`birth_year`,`profile_pic`,`mobile_number`,`last_seen_on`,`allow_last_seen`,`number_of_share`,`date_of_joining`,`date_of_expiry`,`user_status`,`reg_status`,`friend_user_list`
                      From `user`
                      WHERE `user_id`='".$user_id."' and `device_id`='".$device_id."' and `reg_status`=1";
                
            $recordset = mysql_query($query);
                
            $user_record = array();
                
            if(mysql_numrows($recordset)>0){
                $user_record                = mysql_fetch_assoc($recordset);
                
                $respArr['respStatus']    = 200;
                $respArr['respMsg']       = $message["user"]["getDataSucc"];
                $respArr['userDetail']    = $user_record;
                
            }else{
                $respArr['respStatus']    = 400;
                $respArr['respMsg']       = $message["user"]["getDataFail"];
                $respArr['userDetail']        = "";
            }
                
            return $respArr;
        }
        
        public function update($arr)
        {
            global $message,$connectDb,$gcm;
            
            $user_id            =   $arr[0];
            $nick_name          =   $arr[1];
            $profile_pic        =   $arr[2];
            $allow_last_seen    =   $arr[3];
            $user_status        =   $arr[4];
            $device_id          =   $arr[5];
            $birth_year         =   $arr[6];
            
            $last_seen_on       =   date ('Y-m-d H:i:s');
            
            $query = "";
            
            $update_clauses     =   array();
            
            $flag = $updatedNickName = $updatedProfilePic = false;
            
            if(trim($nick_name)!=""){
                $updatedNickName = true;
                $update_clauses[] = "`nick_name`='".$nick_name."'";
            }
            if(trim($profile_pic)!=""){
                $flag = true;
                $updatedProfilePic = true;
                $update_clauses[] = "`profile_pic`='".$profile_pic."'";
            }
            if(trim($allow_last_seen)!=""){
                $update_clauses[] = "`allow_last_seen`='".$allow_last_seen."'";
                if($allow_last_seen=="0"){
                    $last_seen_on = "";
                }
                $update_clauses[] = "`last_seen_on`='".$last_seen_on."'";
            }
            
            if(trim($user_status)!=""){
                $message .="status,";
                $update_clauses[] = "`user_status`='".$user_status."'";
            }
            
            if($birth_year!=""){
                $update_clauses[] = "`birth_year`='".$birth_year."'";
            }
            
            $update_clauses = implode(",",$update_clauses);
            
            $query =   "UPDATE `user` SET $update_clauses
                        WHERE `user_id`='".$user_id."' and `device_id`='".$device_id."' and `reg_status`=1";
                
            mysql_query($query);
                
            $respArr = array();
                
            if(mysql_affected_rows()>0){
                
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = $message["user"]["updateDataSucc"];
                $respArr['userID']          = $user_id;
                $respArr['respRowCount']    = "1";
                
                $receiver_dcm_key = $connectDb->getFriendsGCMArray($user_id);
                if (($updatedNickName || $updatedProfilePic)&& count($receiver_dcm_key)>0){
                    $gcm_notification = array();
                    $message_id  = $connectDb->create_message_id($user_id,0);
                    $gcm_notification['GCM_NOTI_TYPE']    = 41;
                    
                    if($updatedNickName && !$updatedProfilePic){
                        $gcm_notification['GCM_NOTI_TYPE']    = 41;
                    }else if(!$updatedNickName && $updatedProfilePic){
                        $gcm_notification['GCM_NOTI_TYPE']    = 42;
                    }else{
                        $gcm_notification['GCM_NOTI_TYPE']    = 43;
                    }
                    
                    $gcm_notification['GCM_DATA_PACKET']  = array("msg_id"=>$message_id,"user_id"=>$user_id);
                    
                    $gcm->send_notification($receiver_dcm_key,$gcm_notification);
                    
                }
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = $message["user"]["updateDataFail"];
                $respArr['userID']          = "";
                $respArr['respRowCount']    = "0";
            }
            
            
            return $respArr;
        }
        
        public function sendVerificationCode($user_id)
        {
            $code = $this->generateVerificationCode();
                
            $mobile_number = array();
                
            $query = "UPDATE `user`
                      SET `verification_code`='". $code ."', `reg_status`='2'
                      Where `user_id`='".$user_id."'";
                
            $mobile_number = mysql_fetch_array(mysql_query("SELECT `mobile_number` from `user` WHERE `user_id`='".$user_id."'"));
                
            mysql_query($query);
                
            if(mysql_affected_rows()>0){
                return true;
            }else{
                return false;
            }
        }
        
        public function updateUserStatus($arr)
        {
            
            $user_id     =   $arr[0];
            $status      =   $arr[1];
            $device_id   =   $arr[2];
            
            $query = "UPDATE `user`
                      SET `user_status`='". $status ."'
                      Where `user_id`='".$user_id."' and `device_id`='".$device_id."' and `reg_status`=1";
                
            mysql_query($query);
            
            $respArr = array();
            if(mysql_affected_rows()>0){
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = $message["user"]["updateStatusSucc"];
                $respArr['userID']          = $user_id;
                $respArr['respRowCount']    = "1";
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = $message["user"]["updateStatusFail"];
                $respArr['userID']          = "";
                $respArr['respRowCount']    = "0";
            }
            return $respArr;
        }
        
        public function updateRegStatus($arr)
        {
            
            $user_id    =   $arr[0];
            $status     =   $arr[1];
            $device_id  =   $arr[2];
            
            $query = "UPDATE `user`
                      SET `reg_status`='". $status ."'
                      Where `user_id`='".$user_id."' and `device_id`='".$device_id."'";
                
            mysql_query($query);
                
            $respArr = array();
            if(mysql_affected_rows()>0){
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = $message["user"]["updateRegStatusSucc"];
                $respArr['userID']          = $user_id;
                $respArr['respRowCount']    = "1";
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = $message["user"]["updateRegStatusFail"];
                $respArr['userID']          = "";
                $respArr['respRowCount']    = "0";
            }
            return $respArr;
        }
        
        public function updateExpiryDate($arr)
        {
            $user_id        =   $arr[0];
            $expiry_date    =   $arr[1];
            $device_id      =   $arr[2];
            
            $date_of_expire = mysql_fetch_assoc(mysql_query("SELECT `date_of_expiry` FROM `user` where `user_id`=".$user_id." and `reg_status`=1"));
                
            $query = "UPDATE `user`
                      SET `date_of_expiry`='". date('Y-m-d H:i:s',strtotime($date_of_expire['date_of_expiry'].$expiry_date)) ."'
                      Where `user_id`='".$user_id."' and `device_id`='".$device_id."' and `reg_status`=1";
                
            mysql_query($query);
                
            $respArr = array();
            if(mysql_affected_rows()>0){
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = $message["user"]["updateExpiryDateSucc"];
                $respArr['userID']          = $user_id;
                $respArr['respRowCount']    = "1";
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = $message["user"]["updateExpiryDateFail"];
                $respArr['userID']          = "";
                $respArr['respRowCount']    = "0";
            }
            return $respArr;
        }
        
        public function updateAllowLastSeen($arr)
        {
            $user_id            =   $arr[0];
            $allow_last_seen    =   $arr[1];
            $device_id          =   $arr[2];
            
            $query = "UPDATE `user`
                      SET `allow_last_seen`='". $allow_last_seen ."'
                      Where `user_id`='".$user_id."' and `device_id`='".$device_id."' and `reg_status`=1";
                
            mysql_query($query);
                
            $respArr = array();
                
            if(mysql_affected_rows()>0){
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = $message["user"]["updateLastSeenSucc"];
                $respArr['userID']          = $user_id;
                $respArr['respRowCount']    = "1";
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = $message["user"]["updateLastSeenFail"];
                $respArr['userID']          = "";
                $respArr['respRowCount']    = "0";
            }
            return $respArr;
        }
        

        public function updateProfilePic($arr)
        {
            
            $user_id        =   $arr[0];
            $profile_pic    =   $arr[1];
            $device_id      =   $arr[2];
            
            $query = "UPDATE `user`
                      SET `profile_pic`= '". $profile_pic ."'
                      Where `user_id`='".$user_id."' and `device_id`='".$device_id."' and `reg_status`=1";
                
            mysql_query($query);
                
            $respArr = array();
            if(mysql_affected_rows()>0){
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = $message["user"]["updateProfilePicSucc"];
                $respArr['userID']          = $user_id;
                $respArr['respRowCount']    = "1";
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = $message["user"]["updateProfilePicFail"];
                $respArr['userID']          = "";
                $respArr['respRowCount']    = "0";
            }
            return $respArr;
        }
        
        public function updateNickName($arr)
        {
            $user_id        =   $arr[0];
            $nick_name      =   $arr[1];
            $device_id      =   $arr[2];
            
            $query = "UPDATE `user`
                      SET `nick_name`= '".$nick_name."'
                      Where `user_id`='".$user_id."' and `device_id`='".$device_id."' and `reg_status`=1";
                
            mysql_query($query);
                
            $respArr = array();
            if(mysql_affected_rows()>0){
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = $message["user"]["updateNickNameSucc"];
                $respArr['userID']          = $user_id;
                $respArr['respRowCount']    = "1";
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = $message["user"]["updateNickNameFail"];
                $respArr['userID']          = "";
                $respArr['respRowCount']    = "0";
            }
            return $respArr;
        }
        
        public function getExpiryDate($user_id)
        {
            $query = "SELECT `date_of_expiry`
                      From `user`
                      WHERE `user_id` ='".$user_id."' and `device_id`='".$device_id."' and `reg_status`=1";
                
            $recordset = mysql_query($query);
                
            $user_record = mysql_fetch_array($recordset);
                
            if(!empty($user_record)){
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = $message["user"]["getExpiryDateSucc"];
                $respArr['expiryDate']      = $this->getDateDiff(strtotime($user_record['date_of_expiry']),time());
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = $message["user"]["getExpiryDateFail"];
                $respArr['expiryDate']      = "";
            }
            return $respArr;
        }
        public function getUserArrayByMobileNumbersTrial($arr)
        {
            global $message,$connectDb;
            
            $mobileNumbers  =   $arr[0];
            $user_id        =   $arr[1];
            $device_id      =   $arr[2];
            
            if($connectDb->verify_user($user_id,$device_id)!=0){
                
                $unregFrnd = array();
                $regFrnd   = array();
                $respArr   = array();
                $i=0;$j=0;
                
                $consider_mobileNumber = array();
                
                foreach(explode(",",$mobileNumbers) as $mobileNum){
                    
                    if(strlen($mobileNum) >= 10){
                        $consider_mobileNumber[] = substr($mobileNum,-10);
                    }
                }
                $mobileNumberList = implode(",",$consider_mobileNumber);
            
                $query =   "SELECT `user_id`,`friend_user_list`,`nick_name`,`profile_pic`,`mobile_number`,`last_seen_on`,`user_status`,`reg_status`
                            From  `user`
                            WHERE SUBSTRING(`mobile_number`, -10) IN($mobileNumberList)";
                //echo $query;
                $resultSet = mysql_query($query);
                $regUsersList = array();
                if(mysql_numrows($resultSet)>0){
                    while($row = mysql_fetch_assoc($resultSet)){
                        $regUsersList[] = $row;
                    }
                }else{
                    return array();
                }
                
                $respArr['respStatus']          = 200;
                $respArr['respMsg']             = $message["user"]["friendlist"];
                $respArr['respRegRowCount']     = count($regUsersList);
                $respArr['respRegList']         = $regUsersList;
                $respArr['respUnRegRowCount']   = count($unregFrnd);
                $respArr['respUnRegList']       = $unregFrnd;
                
                return $respArr;
                
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "You are unauthorized for sending this request";
                return $respArr;
            }
            
        }
        
        //List of comma seperated mobile numbers
        public function getUserArrayByMobileNumbers($arr)
        {
            global $message,$connectDb;
            
            $mobileNumbers  =   $arr[0];
            $user_id        =   $arr[1];
            $device_id      =   $arr[2];
            
            if($connectDb->verify_user($user_id,$device_id)!=0){
                
                $unregFrnd = $unregUsersList = array();
                $regFrnd   = array();
                $respArr   = array();
                $i=0;$j=0;
                
                $consider_mobileNumber = array();
                
                foreach(explode(",",$mobileNumbers) as $mobileNum){
                    
                    if(strlen($mobileNum) >= 10){
                        $consider_mobileNumber[] = substr($mobileNum,-10);
                    }
                }
                $mobileNumberList = implode(",",$consider_mobileNumber);
                
                //Get registered Users
                $query =   "SELECT `user_id`,
                                    IFNULL(`friend_user_list`,'') as 'friend_user_list',
                                    IFNULL(`nick_name`,'') as 'nick_name',
                                    IFNULL(`profile_pic`,'') as 'profile_pic',
                                    `mobile_number`,
                                    IFNULL(`last_seen_on`,'') as 'last_seen_on',
                                    IFNULL(`user_status`,'') as  'user_status',
                                    `reg_status`
                            From  `user`
                            WHERE SUBSTRING(`mobile_number`, -10) IN($mobileNumberList)";
                //echo $query;
                $resultSet = mysql_query($query);
                $regUsersList = array();
                $friendListUpdate = array();
                while($row = mysql_fetch_assoc($resultSet)){
                    $regFrnd[] = substr($row['mobile_number'],-10);
                    $regUsersList[] = $row;
                    $friendListUpdate[] = $row['user_id'];
                }
                    
                $unregFrnd = array_diff($consider_mobileNumber,$regFrnd);
                $insertNewQuery = "";
                
                if(count($unregFrnd)>0){
                    $insertNewQuery = "INSERT INTO `user`(`mobile_number`,`reg_status`,`friend_user_list`)VALUES";
                    foreach($unregFrnd as $newUser){
                        $insertNewQuery .= "('".$newUser."','5','".$user_id."'),";
                    }
                    $insertNewQuery = rtrim($insertNewQuery,',');
                    mysql_query($insertNewQuery);
                }
                
                $selectQuery = "SELECT `user_id`,`mobile_number`,`reg_status`
                                From  `user`
                                WHERE SUBSTRING(`mobile_number`, -10) IN(".implode(",",$unregFrnd).")";
                    
                $respQuery = mysql_query($selectQuery);
                    
                while($row = mysql_fetch_assoc($respQuery)){
                    $unregUsersList[] = $row;
                    $friendListUpdate[] = $row['user_id'];
                }
                $updateFriendQuery = "UPDATE `user` SET `friend_user_list`='".implode(",",$friendListUpdate)."' WHERE `user_id`=$user_id";
                $respUpdateQuery = mysql_query($updateFriendQuery);
                //Get All Active User
                $query =   "SELECT `user_id`,
                                    IFNULL(`friend_user_list`,'') as 'friend_user_list',
                                    IFNULL(`nick_name`,'') as 'nick_name',
                                    IFNULL(`profile_pic`,'') as 'profile_pic',
                                    `mobile_number`,
                                    IFNULL(`last_seen_on`,'') as 'last_seen_on',
                                    IFNULL(`user_status`,'') as  'user_status',
                                    `reg_status`
                            From  `user`
                            WHERE SUBSTRING(`mobile_number`, -10) IN($mobileNumberList) and `reg_status`=1";
                //echo $query;
                $resultSet = mysql_query($query);
                $regUsersList = array();
                while($row = mysql_fetch_assoc($resultSet)){
                    $regUsersList[] = $row;
                }
                //Get All Active User
                $query =   "SELECT `user_id`,
                                    IFNULL(`friend_user_list`,'') as 'friend_user_list',
                                    IFNULL(`nick_name`,'') as 'nick_name',
                                    IFNULL(`profile_pic`,'') as 'profile_pic',
                                    `mobile_number`,
                                    IFNULL(`last_seen_on`,'') as 'last_seen_on',
                                    IFNULL(`user_status`,'') as  'user_status',
                                    `reg_status`
                            From  `user`
                            WHERE SUBSTRING(`mobile_number`, -10) IN($mobileNumberList) and `reg_status`!=1";
                //echo $query;
                $resultSet = mysql_query($query);
                $unregUsersList = array();
                while($row = mysql_fetch_assoc($resultSet)){
                    $unregUsersList[] = $row;
                }
                $respArr['respStatus']          = 200;
                $respArr['respMsg']             = $message["user"]["friendlist"];
                $respArr['respRegRowCount']     = count($regUsersList);
                $respArr['respRegList']         = $regUsersList;
                $respArr['respUnRegRowCount']   = count($unregUsersList);
                $respArr['respUnRegList']       = $unregUsersList;
                
                return $respArr;
                
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "You are unauthorized for sending this request";
                return $respArr;
            }
        }
        
        public function resync_app($arr){
            $user_id                =   $arr[0];
            $device_id              =   $arr[1];
            $last_sync_timestamp    =   $arr[2];
            
            // Get All Requests
        }
        
        protected function check_user_exist($mobile_number){
            $query = "SELECT count(*) as `count`,`user_id`,`reg_status`,`friend_user_list`
                      From  `user`
                      WHERE SUBSTRING(`mobile_number`,-10) = '".substr($mobile_number,-10)."'";
                
            $user_record = mysql_fetch_assoc(mysql_query($query));
                
            return $user_record;
        }
        protected function check_friend_exist($mobile_number){
            $query = "SELECT `user_id`,`friend_user_list`,`nick_name`,`profile_pic`,`mobile_number`,`last_seen_on`,`user_status`,`reg_status`
                      From  `user`
                      WHERE `mobile_number` like '%".$mobile_number."'";
            $resultSet = mysql_query($query);
            
            if(mysql_numrows($resultSet)>0){
                return mysql_fetch_assoc($resultSet);
            }else{
                return array();
            }
            
        }
        protected function friend_registration($friend_mobile_list,$user_id)
        {
            global $message;

        }
        protected function generateVerificationCode($length=6)
        {
            $code = '';
                
            $keys = array_merge(range(0, 9), range('a', 'z'));
                
            for ($i = 0; $i < $length; $i++) {
                $code .= $keys[array_rand($keys)];
            }
                
            return $code;
        }
        
        public function no_of_users(){
            $query = "SELECT count(user_id) as 'no_of_users'
                      From  `user`
                      WHERE reg_status=1";
                
            $resultSet = mysql_query($query);
                
            if(mysql_numrows($resultSet)>0){
                return mysql_fetch_assoc($resultSet);
            }else{
                return array();
            }
        }
        
        protected function sendSms($number_array,$code)
        {   
            $url = "http://www.freesmsgateway.com/api_send";
            $num_arr = array();
            $post_content = "";
            
            if(!is_array($number_array))
            {
                $num_arr = explode(",",$number_array);
                $post_content = json_encode($num_arr);
            }
            else
            {
                $post_content = json_encode($number_array);
            }
            print_r($post_content);
            
            $fields = array(
                        'access_token'=>'76cc7417603602b8d5d739da2b351bb4',
                        'message'=>urlencode("Locator Verification code is: ".$code.".<br> Enjoy locating people/places with ease"),
                        'send_to'=>'post_contacts',
                        'post_contacts'=>urlencode($post_content),
                      );
            
            $fields_string=""; // Below code will convert above $field array in Query String to pass in post request
            
            foreach ($fields as $key=>$value)
            {
                $fields_string .= $key ."=" . $value . "&";
            }
            
            rtrim ($fields_string,"&");   //removes '&' at the end
            
            //open connection
            $ch = curl_init();            // cURL library is HTTP Client Library in PHP
            
            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_POST,count($fields));
            curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            
            // execute post
            $result = curl_exec($ch);
            
            // close connection
            curl_close($ch);
            
            print $result;
            
            return true;
        }
        
        public function gettry($data)
        {
            $query = "SELECT `user_id`,`dcm_code`,`device_id`,`device_type_id`,`nick_name`,`profile_pic`,`mobile_number`,`last_seen_on`,`allow_last_seen`,`number_of_share`,`date_of_joining`,`date_of_expiry`,`user_status`,`reg_status`,`birth_year`,`friend_user_list`
                      From `user` ORDER BY `user_id`";
            $recordset = mysql_query($query);
            $arr = array();
            while($user_record = mysql_fetch_assoc($recordset)){
                $arr[] = $user_record;
            }
            
            return $arr;
        }
        public function flushtable($arg){
            
            $query = "TRUNCATE TABLE `user`";
            $recordset = mysql_query($query);
            return "done";
        }
        public function print_image($byte_array){
            $byte_array = "\/9j\/4AAQSkZJRgABAQAAAQABAAD\/2wBDABsSFBcUERsXFhceHBsgKEIrKCUlKFE6PTBCYFVlZF9VXVtqeJmBanGQc1tdhbWGkJ6jq62rZ4C8ybqmx5moq6T\/2wBDARweHigjKE4rK06kbl1upKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKT\/wAARCAMAAwADASIAAhEBAxEB\/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL\/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6\/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL\/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6\/9oADAMBAAIRAxEAPwDoKKKKQwooooAPcdakVt31qOj3HWgRNRTVbd9adTAiljzyOtRq2eD17GrNRSx7uR1pAOR88Hr\/ADp9VlbPyt19fWpkfs34GgB9NZc8jrTqKYEP86KkZd31qPvg9aQBRRRQMKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACijNH4UAKpw49+KlqA9OOtTKdyg+tCEQgYyvpSJ8pYCnyDDA+vFC7Su6gBMk0BTT6KAGdDimSjjPpUjjjPpSEZGKTQFeil6UlZEhRRRQAUUUUAFFFFABRRRQAUUUUAFFFFACqadTKfWkSkFPQ5GKZSqcGqGSUUUUAFFFFABRRRQAqHa2Ox6VIyhhg1ERkYp8bZGD1FAiMZBIPUVE4AYgVZkXIyOoqFxuXI60ANizv49Kljk3A57Goo+Nx9BTohwTQBJmk3bTnt3oooAl4IqvImOPyqSNsHafwp7ruHv2oAqAZOKnqPb84Pvz9akoAKKKKACijNKFY9sfWgBKM56c08RjvzTgAOlFgIwjH2pwQD3+tPopgFFFJQAtFJS0AQ0UUUhhRRRQAUUUUAHuOtSK2761HR9OtAiaimq27606mBFLHu5HWo1b+Fv\/11ZqKWPdyOtIBVfHDfgaWVA6EEkVCHwMN+dSq2OD09aAII5mDKhORnGassoYe\/rSkA9QDS0wIeQcHrRUrKGHNREEHBpAFFFFAwooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACjvzRSf0oEOoPPFKBg5pevagBnanRHqv40opg+Vh7cUASONykd+1Qp90VYqEjDkfiKGARt8g9qXdTVU8+madtoATJop2BTTw31oAikGGz60yppBlfpUNZtaiYUUUVIgooooAKKKKACiiigAooooAKKKKACn0ynDpVxGhaKKcEPfirKFQ5H0p1IFApaACiiigAooooAKQ5ByOopaKAJVIYZFROu1s9jQp2N7GpSARg9KBEG3AbHGaIxhBS8qdppaACiiigYhGfrUqNuHuOtR0JncCvTvQIWRcHcPxpMipqQKB0AFAEYVj2x9acIx3OafRTAYWSMckCoftLbsBQ3pip2RXxuGcUYXI4GR0oAUZxyMGlpOfpRj1oAM0c0KQwBUgg9CKWgBMUHgHAz7UZ9KM\/hQBV\/fbjncu484qxHH5YPzE59aRmI5HPrTWZsEk0XAKKKKQwooooAKKKKACiiigA\/nUitu+tR0UCJqKajbuD1p1MCOSPdyOtQgleO3pU\/mpkjcAR602WMSDI60gEjkH4fyqaqmDjIz\/UU+OXbw3T1oAsUhAIwaOtLTAhIKnB\/OipSARg1GQVPPT1pAJRRRQMKKKKACiiigAooooAKKKKACiiigAooooAKKKKACkIyDS0UALGcqKdkVEBjI96WgQ\/cKafmz70lLQBJGcqPXoabKOA3pSRnDEevNSEZBB70wIYzyy++akqE5Vge4ODUu6kAuRSN0+lMbnn6UtABUBGCRU9RSjnPrUyBjRSUUVmSFFFFABRRRQAUUUUAFFFFABRRRQAVJGuRk1HUkXQ1UdxokAx0ooorQoKKKKACiiigAooooAKKKKAAjIxTo2\/hPUU2kOeo6igRJIu4cdR0qMHIqVW3LmkMeWyDjPWgBlKFY9sfWnhQvQU6iwDAgHXn60+iimAUU3d6c0ooACQKTJPQfnUF5eC0Me+N2VzjcvapCyXVs3luGV1IyDQBJjPU5ppkjVghdQx6Ank1maVM0EcUchPlSj5GP8LZ5X\/Cla2QyNZhEklZS7zSDnk9qQFqW6cXS20cfznnLnAK98e9ROA+qGO4Z9jIDEAxAz36d6gi3XEbQFyZ7c5hmA4YDjr+hq8yC5gRbiIA8EjP3T7EUAQRMun3Qti37iXmPJ+6e4+lXXkCg45NZ09mz74o4cbsAzSPuOB6d6uSHCgZzQAQuT8p\/Cpaqjg5FWEbcufzoAdUMoIXHapqRhkYoAbRRRQMKKKKACiiigAooooAKKKKACpEbdwetR0UCGyQElnB5znFSQsWQZXbTkbPB606mAx0zyOv86hZc5I69x61Zpjpnkdf50gIY5NnB5X+VWAcjIquy7uRwe9EchjOP4e49KALNIQCMGgEEZHSlpgRMpX6etJUtRsu3kdP5UgEooooGFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFGaACiilUBhQITFGDT6KAIycc9wamqNhg59adEflx6cUAMlXn6\/zop8gyvHUc0ygAooooGFNcZU06ikIr0UrDDEUlZkhRRRSAKKKKACiiigAooooAKKKKACnxfeP0plOj++Ka3BE1FFFalhRRRQAUUUUAFFFFABRShWPQY+tOEY780CGDJ6DNOEf94\/gKkopgIAAMClopM0ALSZxRyfamuyxqXcgAdSTQA7JPQUY9earm8iMLyRkSFBkhTmq8N6zCOR7m1Ct95M4Kj6560AW5rqC3IEsqoT2J5qFporUo8aL5ErZaRW4BPeopXSCVWhdM3THMznKqAOlRWyqk0lqubi1k6sACFbv0pAaU8KTwtG4yG\/T3rPt0cSOiuIruP7x\/hlXsSP61bs1kgiEUrBgpwjDrjtmpyiFw5VSwGAcc4oAz7e3eS1nhuI9mZSykHpnkEVM9tHNGguQszqPvYxmpjkHaaKBiKqooVFCqOgAxS0UUAFRy9RUlIy7h70AQ06Ntrex602igRaoqONjs\/lS5oAKKKKBhRRRQAUUUUAFFFFABRRRQAUUUUAFSI2eD1qOigRNRTEbPB60+mAx0zyOv86iZd3swqxTWTd7H1pAV0cxnpx3FWVYMMg5FQMueDwwpqsyNx17j1oGWqKajhxkUpOBmmIYyY5Xp6U2nRyrJnHBHY0MufmX\/APXSsA2iiigYUUUUAFFFFABRRRQAUUUUAFFFHegAoXpS0YxQIQ9aSM4dlP1px6U0\/eBFAD9wpNxoUZAPrS4FADc560qnDj34pWGRx1FMPI4\/CgCeocYJX0qVTuUH1pkgwQ34GgBtFFFAwooooAjlHQ1HU7DKkVBWclqSwoooqRBRRRQAUUUUAFFKAT0FOEbfSnYBlFSiIdzShFHanysdiGnKrZHFTAAdKKfKFgoooqygoooHPQZoAKKcIyep\/AUoaNeAyj8aLCGhGPXinhAPr60oIIyDkUtMAoopKAFpKa7pGu6R1QerHAqC5vUht\/PQecmcEoQQKALNU9ReWJUZXKQ5xIVHzAetNklu7eMXEjRyx9XRFxtHqD3q4Ck0WRh0cfgRQBWazPl7oLiZX6hjIWB+oPFVbmaS4tVkMWXt5P3sXb61LE89kWthC8yjmJgeMehJ6YqW1ieIyySEeZK2SB0HoKQFWIwo63klzGXxgJCOvt6mrLrOJC0C2+1h\/EhBH+NSLFGrlxGgc9WCjNPoAhgtkit\/JfEgyScrxn6VKoCKFRQqjoAMClooGB5p8bZGD1FMoyQcjtQIkddw46jpUYORUoIIyKjcbTnsetACUUUUDCiiigCORe\/50yp+tRbcPigRIowoFLRRQAUUUUDCiiigAooooAKKKKACiiigAooooAKKKKACnq+eD1\/nTKKBE1FRq+OG\/OpKYDWUMOaidezdexqekIBGDQBVBZG44P8AOrEcgce\/pTHTsenY1Fyre\/Y+tIY+aF2csoA+hqWIERgMMEUkcgcYPBqSmIYyZ5HWmVNTWXd7H1pAR0Ucg4PWigYUUUUAFFFFABRRRQAUdOaKCMgigBcE+1LgUkbbkHrTqBCFQQRTRT6YevHSgAjBC9cjtT6ijJC49DTsmgB2RTf5UUUAOiPVfxpzDcpFRg4YH8KmoAgByKWhhhz780UDCilwaQ8GgAqFxhjU1NZQ3WpauJkNL16VKEUdqd06UuUViEIx7U4RHuakop8qHYYI1HXmnBQOgpaKdgCiiimMKKKKACijNKEY+1ACUAE9B+NPCAe596fRYQwR\/wB45pwGOlLRTAZIZMDywCfeoTbsxBZsknmrNMlljhTfK4RfUmgBwAAAHQU2Tf5beXt34+Xd0zVeS+T7O01vtmCcsAcED1qLN41ut1FOHJG7ygowR6Z65oAdcySoq3EbhjDxNGhyCO\/4ihbm7lTz4IY2h5wpYhmHr6CoWmWNk1CEfuJcLMvofWpIlmtZWjgjWW3cb0JbAX2zSAgubjfJBdoivHtKYk6RtnqalijhhkZpZkc3Hy+VGPlOfb+tS2kLQrMZNpaVyxUfdFSRwxRkmOJEJ67VAoAbaJJArwSfPGp\/dsTnK+h+lLbwrbxmONm2kkgE\/d9hUlFABRRRj1oAKKXApKACiiigYUUUUAKhwcdj0qQgEYNREZFSI24c9R1oQiPBBIPainyLkZHUUzrQAUUUUDCjFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABSq23g9P5Uh4pAc9AT9KAJqWmRqw64A9KfTEIRkYNROmBzyv8qmooAqEFT1+hqaKXdw3Wh0wPVf5VCylfp2NIZboqGKX+FqmpiEZQw5qIgg4NTUhAIwaAIqKUgr16etJSGFFFFABRRRQAUUUUANX5XIHenZNGOaKBBRRRQAgGKWiigYUUUUABGRipIzlR69DUdLGcOR60CHSj5c+lMjOcj0NSnkVXX5JMfgaAJqRuR7ikLU1SQWFAC0UUUDCiiigAooooAKKKACegoAKM04RnufypwUDoKBEYDHoPzpwj9TmpKKYCAAdBiloooAKKKKAILydra3aVYy+3qM4qKJJ5wsslwFQ4YLFwPxJ61bIBBBGQayo7dIbz7LOXaB+YVLfL7gikBPqFxKnlmN1WCQhWkXkoc9abqKSwm3uE\/eCHIbeM9R944q48ETwGEoojIxtAwKjtVaC3WJ5PNK8AgY47UAVY5IEmW4a5+0TuNoSIAZH0\/xqxawNaSyoGH2dvmQZ5U9x9KkVVUkqipnrtGM0tADI4Y4\/M2JgSnLA8j8qfRRQMKKKKACiiigApQc0lFAhaCM0UUAJRQRRQAUUUUDClAIOaZE\/JB79KloELTHGDu\/OnD0p1AEVFBG04\/KigYUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFI7EIxRd7gZC5xmgBaDwCSQAO5oQSMillCMRyM5waztSSOG\/gnuf3ls\/wAjK3Kq3Y4\/z0oAupcQSvsjmjdv7qsCaSeeOAKJm27+FUAlm+gFUbx1d4JoLOURQPvMiptO30AOCRT7mZFu7bU0BkgKFGIH3AehoAdo8mRLaS7maE5UuCCyHocGtPpWRLP9pv4LmwUymPKyEcAqe2T371pMMnk5+tAh5kXtz9Kb5jZ6DHpSUUASggjIpahBKnI\/EVKCCMimAtROu3\/d\/lUtFAFVl289vX0p8cmPlanMu3kdP5VEyY+709PSkMs0tVo5CpwTx71YByMimICMjBqNl2\/T+VS0UAQ0U5kxyvT0ptIYUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUh459Oadg0u2gQ8c1HIvzg+tOj4BX0\/lSuu5SO\/amBHRjnNAORRSGFFFGaACilCse2PrThGO\/NAhg56DNOCE9Tin0tFgGhFHbmnUUUwCiiigAooooAKKKKACimlwPc+1MLsenFAEhIHU4qKTZJjMattOQWHQ+ooxRSADk9TmiiigYUUUUAFFFFABRRRQAUUUUAFFFFAAOKWkoHpQIWkPFLkUmfagApHOFNKKQjIIoGRxEB+R16VYqr0P0qwrhvr6UCHUoOaSjpzQAOuR70wVLUbAZyKAEooooGFFFFABRRRQAUUUUAFFFR3Ey28DzMGYIMkL1oAkopqurxCVSPLK7txOBioFv7UuoaR8OdqtsIUn60AWMih2EaF3IRFGSzHGKhjujFdm3uUWMsf3Tj7rj0+tV7NVl0+cTq022ZztycsQeBQImjvrd5URvNHmcIzIVVj7UyGSebUZLeWQRCEh1WMY8xT0z7D+tUZZhqFquZ2kuXOY4Y+BGR6\/4mrwgmnktbt\/3MyDEinuPTigYlu32LVJbcn93cfvY\/8Ae7j+tWbyIXltJARt3DgnsexpZIYpGRmQExtuQ9MGn0CI7UzG2j+0DEoGGGc0W1vHaxlIQQpYtgnOPpUlFAwzRRRQAUUUUAFAJU5H4iiigCUEEZFLUIJU5H5VKCGGRTELUbLjkdKkooAhwD70L8n3enpTmXHI6elNpASqQwyKWoRkHI61IrBvY+lMB1MZM8jr\/On0UAQ0VIyhvY+tRnIOD1pAFFFFAwooooAKKKRuFyKAHYNLtoByMiloEJgUtFFABRSZFJmgBSdrA\/gakqE88YzUiZ2jPWgCNxtb2PNABPQVLS0WAjEfqfyp4AHQYpaKYBRRRQAUUUUAFFFFABRTS6jvn6U0uT0GPrQBJTSyjqajJJ6k0cClcCUEEZHSmS549KIjwR6GnONyEd+1MCOigHIzRSGFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFAEJXDYo3kPuFSMDncOwqGgRa3DGaTdSAYGKKAFB7GnBQwINMqRDlaAI+Rweoop8i\/xDqKZQAUUUUDCigZP3QTVPUpJY\/s8Kv5YnkCM69QPY+tAFzFIThSx4UcktwBVO5stOtlRnzE+fldCS5P6k1HqNwl1YxToGaBJx5qkc4B5yKBFuC6tbiQxx3KM391T1\/xqEPNd3dxbxOkUUOFbKbmfP17VHq8lvLYq8LqZQVMJQjOc9qkltbhbk3VrJGkkigSI+dp9+KAIrGNRFcaXdfOIjlcHG5DyP8APvVeW4TULI+ZNh2yI7aPGQR0z3\/kKvxWbC5F1PKWmAxhOFx6VZCIrFlRQx6kDk0DK8cbXunol7GQxGGB65HenWVsLSAxiQyEsWLEetT0UAAwMkAAnrgdaKKKACiiigAooooAKKKKACiiigAooooAKASpyKKKAJQQwyKWoQSDkVIrBhxTEOqNk7r+VSUUAQ0VIyZ5HBqPvg8GkA9Xzwev86fUNOV8cN09aAJKQgEYNLRTAiIK9enrSVL1qNlK8jkfypAJRRRQMKRhlSKWigBqMQuAaXc3rSYwTSUCHBj60tIqMe1PEfrQA0e1OC5pwUCnUANC4p1FISB1pgLRSdaKAFoqJ7iBGKvMisOoLCpFYMoZSCD0IoAWikpu8dBzQA+mlgOpqOWQRxtJK21FGTjtTVeMyCNW+YrvA9vWkBIXJ6DH1pp56nNKV4460mVClmICjqTQAUUfTkH0oOaACgD86UcjiigBFOJB78GpqhZcjjrUoORmgCLGGIop0g5B\/Cm0AFFFFAwopcGkoAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAqMphxjpUlFABRRRQIKFO1s9j1oooGTVEw2t7HpSxt\/Ce3Snsu4YNMRFRR7HqKKQyXpVTUIFvLYxBsODuRh2I6VLjPXn60tAiqlzfhAr2WZBxuEi7T7+o\/KlsrU26ymQq0kzl2Cj5R7CrNFAyOO3gibdFBGjeqqAakoooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKOhyODRRQBIrbuDwadUNPV+zdfWgQ+msoYf1p1FMCEgg4NFSkAjBqNlK\/T1pACsV9x6VICCMioqAdpyKAJqKQHIzjFLTAjZO6\/lTMjucVPSUgIcj1pfbqalKg9QKAAOgxRYCMIT14p4UDoKdRTAKKKYZB25oAfTWcL1NMLMe+PYUqx92\/KgABd+g2j1NOCge59TTqKACiimFxnA5oAzEks0v70XRiyWXG8A\/w02K4+y215cWyfuA4MQIwD0Bx7VZggYXd1JIgw7KVJ78UupRPPp8sca7nOMD8RSAUveCHJjjaRmAUAnCj39fwpkVxcfaHt5PKL7N6sucdcYIpdSjkkiQIhkUOC6A4LL6VFawsL\/wA1bXyIvKKjpnOR1xQBHZTTQ6XNMdjbCxUc9c96ledI9QWaVgq\/Zsn86gSKYafc23lNu+bB4w2T2qxJYi4uE86PMf2fbn0bNAFm0kllgEkqBCxyqjqB2z71DqA3mG2HSZ\/m\/wB0cn+lS2XniIx3A+aM7Q\/98djULWhuL6WWYOqKoSPaxGe5PFAC6e7C1aNuXgJQ59B0\/TFQi9u\/sa3jRxGLALIM7sdz6U9LZrW7kKBmikj5JbOGH19qrQtPNpMdtHbtl1x5hI2gevrQBfmkuC4W3WMJt3GSTp9MCoDqEgsJZti+ZFJ5ZA6E5A4\/Om3EDrdqz27XMIjCqAR8rfQ1GtpONOuIvJ2u825VBGMZB4oAdfG7xbbzEGNwuAufwz+tWDcXX2sWsaxbvKDlmzgHOOlLqMbukLRoXMUquVHUgURpIdUE5QqjQBeexznFAEcdzfTiWNUhWSE4YnOG9MUHUN1rBJGqiSY4AY8Ke5PtU9rE6XF2zLgO4Kn1GKoCxl+x2+6EO8LsTExHzAk0AWEvJFM8cnlM8cRkVk6EU03l0lvFdOkXktt3KM7gD3zSpErRTiKy8gmMqCcAsT24ouLeV9HjgVCZAiAr9MZoARfP\/tlxlNvlDjB6Z\/nV49arMkiaoJRGzRvGE3DHynPerJ60AFFFFAwooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAPcdRUqncMiosH6UKdrc9D1oEOkX+IdutNqaoWG1sdj0oAKKKKBhRRRQAUUUUAFFFFABRRRQAUUUoGaAEopwFNI2n2P6UCCiiigYUUUUAFFFFABRRRg\/SgAo+lLiigQlFB9aKBhRRRQA5XxwenrUlQ0qsV9x6UCJaKQEEZFLTAjKEfd6ehpyoBz1NOooAKKKYzgcDk0APoqIsx74+lJk\/3jSuBNRUQZh3z9acJB\/FxTAfRRRQBE4OfmOR2pPYdalIBGDSKoX3PrSAFTHJ5NOoopgFNZgvX8qRnxwOtM9+9ACli3XgelJRRSGGaKKMevNAgzRk0tJQBFJlTkHg0+GQ\/dJ+lKQCMGoCCpx3FAFukJpCaSgBThgQelMhjWCFYo87VGBmnUUAGaMmiigYZoOccHmiigCVTuUH1pknBB\/CiI8FfSnONykUxEeTRk0A5GaKQwyaKKKACiiigAoooRgWI7igAo74p9NYcZ7igQlFFLg0DEop22gjIxQIbRQKKBhRRRQAUUUUAFFFFABRRR0oAMetL0oooEFIRmlooAdG2Rg9RSsu4YqM5ByKlBDDIpgRUUUUhhRRRQAUUUUAFFFFACgZpcDGKZG2GKH8KkoEIFx70tFFABR1oooAYRt+nrRTyARg0zocH8DQAUUUUDCiiigApRSUdOaBC0UUUAFJzTgKWgBlFPpnc\/WgAooooGAJByKlU7lzUJ6VMBgYoQhaKKa5wpI60wGu3OB+JptFFIAooooGFFFFACo204PT+VS1D2qRDlQaEIdRRRTAKY79h19fSkZ+y\/iabSAKKPpRQMKKKKAClHNJR0oELQRRQTQAlMkXcMjqKf3ooGFFFFABRRRQAUUUUAFFI33T9KFbfHlhjFACqcOPfipqgIOPeplO5QfWhCIiMMR+NFOlHIb8KbQAUUUUDCiiigAqEMQ24VNUUi4OfWgROCCAR3pahhbB2nv0qagAAAooooAKKKM0ANYYOfzpKUn2pKACiiigYUUUUAFFFFABRRRQAD0paSlB45oEFFAyegzTgnqfyoAaSOlLGGGc9KeAB0GKWmBDRRRSGFLg0J0p1Ahu2l20tITgE+lADR0opB3paBiAgOAe9SVGy7sDPepKBBRRRQAUUUUAFIRkYNLRQAzocHrRTmGRj8jTfY9aACiiigYUUUUAAOBTXfbhgO9OpsgyhoESAgjIORS1XhfbkdR2qYPmgBxOBUYp1NFAC0UUqqW9h60DEAywA7HmpqQAAYFLTEFMk6D60+muNykd6AI6KKKQwooooAKKKKACnx\/c\/E0zBJwOtSgYGBQhC0UUUwIB0paCu3jt2opDAd6CKAcUZz7UCCijpRyaACijH50UDCiiigAooooAKKKKACinBfWoZGMbqfT9aBEwX1pCuDx+VAbIBHeomkKy+3Q+9AEwX1paQEMMg5FLQAzBBx1FOXKr64paQkjpQA5xuQ4\/CowcjNSrjAxUWMEj0NDAKKKKBhRRRQAUhGRilooAgIIPuKsI25c1HIvGaSLvQInpN1NooAXJpKKKACiiigYUUUUAFFFFABRRQAT0FABRmnCP+8fyp4AHQYosIjCMfanhFHufenUhIHU0wFopm49h+dIRnqc0AOLgdOT7U0lj7ClAx0opAMooooGG3Ip9MBxTgcjNAhaKKKAGtjIpKGG3n3ooAQuEI3dDxn0qSoZRmM+3NMhm2\/Kx47H0oAs0UUUAFFFFABRRRQAUjDPsfWlzTSaAEHTmiiigYUUUUAFBGRiiigCHG18VInelZc\/UUKCDQIdSdKOpwOTT1THJ5NACKmeW6elSUUUwCikJAGTUZc5z+nrQBLRSUtADGTuv5Uz271NSEA9RSAiop\/ljsSKTy\/8AaNADaAC3T86kCKO1OosA1VCj+tOoopgFFNLgHH5n0p1ACEAjBqNgVPt2NS0hAIwaAIqO9KQVOD07GkpDFAxRSZ9aM0CFpO9FFABRRRQMKKRjtXPWnqQRkdKBCBfWnU1mCgn0pQQRkdKAFqOf\/V9PxqSggEEHoaAGcAewqFlLzEAirGOMdarzIUbIzg96AJ0QIMDNOpisWUHpSQkFOuT39qAJKKKQmgBy8HA6fypkgwwPrxTo+lEgyh9uaAGUUUUDCiiigAooooAKai7c\/WnUUAFFFFABRRRQAUUUUAFFABPQfnTxH6nNAhnsOaUIx68VIAB0GKWiwDQij3PvTqQsB1NM8zJwo\/OmBJTC4HTk+1Jgn7xzSgAdKQCZY+1RP933NTUx03fjQAsbbl9x1pcimBNnfOaWgBVYEkelOquMiX3zUxNACUUUUDAct+FPGO1MPSlUEZoEOooooAa\/akHSn1GvSgBSMjBqpg5x36VbqCZCrbh0P6UAWVG1QPQUtQwzbvlc89j61NQAUUUUAFITQTSUAFFFFACGilpKACiiigYUUUUAFKoLdOnrSqmeW\/KpKBCKoUcUtFFMAprOF+vpQSTwv50KgXnqfU0AMCsxy3FPVQvQU6igAoophcDgcmgB9FRbm9R9KUSHuPyoAkopnmD0NHmD0NAD6KYJB3BFPoAKY+7HH4+tPooAhHtSo23g9P5U9kDc9D61GQQcGkBKelVw7QuVfLKehqVGxwelDwo5yQc+opgKjCSMEjr2phBU4P4Gnxp5a7c5pxAIwaAIqKCCpwfwNFIYUUUUAFFFFADZPuGkh+4frRKcJ9aIfuH60CFl\/wBWaIAwBz900S\/6s02Bzyv4igCeioxJ+8Knp2qSgApGAZSG6UtITQAz7qfQfnUMJIkGKmfiNue1Qwj95zxgUAWKKKKAFQ4bHqKkqEnBB9DU1CAgHGR6HFLSuMPn1FJQMKKKKACiiigAooooAKKKACeg\/OgAo+nNPEfqc04ADoKLCIwjHrxTwijtzTqQsB1NMBaKjLk\/dH50mGznNAEtRF2OR0x2p6nJx+VNkXHzj8aAG4qMPtkJ7dDUlRyL3pAT0VHC2RtPUVJQAUUm4UmSaAFOMc00UpBxmkoATAznvTsZpiviQg9DxUtADKKKKBgeRTwcgGmUntQIkpNwplFADt1NHU0Ud6AFooooGVZF2uQOlTQzZ+V+vY024HIP4VDQIvE0lIh3KD6iloAKKKKACikooAKKKKBhRRTlQnrwPSgBoBJwKkVQvufWlAAGBS0xBRRRQAUmM0tFABRRRQAU1nC8dT6U12Ibb0Hr602kApJbqePQUlFFAwooooAKKKKACgEr06elFFAEisG+vpTqhpyv2b86BElIQCMGlopgRMpXryPWlRscHp2NPqNkx05FICWio0bHB6djUlMBCARg1EQVOD+BqakIBGDQBFRQQVOD+BopDCiiigBk33PxohHyfjT6RRtXFAhs33PxpsH8VSP9w\/SmQfxUAMcjzCeoqwGBUEelVn+831qdPuL9KAHZoopcetABikYc5HWlJpKACkoooADyMVJGcoPXpUdOjOGI\/GhALKPlz6c0ypTyMVCOBj04oYC0UUUDCigAnoPzp4jHc5oEM+nNOCMevFPAA6UtFgGhAO3NOpCwHU0wyE\/dH50wJKYZAOByfamEE8nJpkhwBikBLlm9hQFAoU7lBFLQAUUUUAJ\/kU8HIzTTQh5IoAYRsbHY9KOtSMu5cVEM9D1HWgBiAiT6VJkbgCetGOc1G4O8Y\/CgCbApaRckDIwaWgAphGD7HpT6axyMUAQyLg57GpUclBnrSMARihRgAUALRRRQMKQ0tFACUUYpcUCEopaKACiiigYjKGGDVZ0KHB\/A1apGUMMGgBlucx49DUtRRoUcjqD3qSgQtJRRQMKKKKACgAk4FKoLdOnrUgAAwKBCKgHJ5NOoopgFFFFABRRRQAUnSkZwOByajJJ6mgBxk\/u\/maVH3exHUUykI7jgjvSAmZQwwaiIKnB6djT0fdweGpxAIweRTAiooYFDzyOxopDCiiigAooooAKKKKACjrQBmnAYoEALKPUelPBDDIptJyDkdf50ASUU1W3fXuKdTAjZO6\/iKEfsfwNSUx03cjrQA+io0bna3WpKAEIBGDURBXr09amooAhopzJjlenpTc0hhRRRQAEZBHrTYl2gj3pXbapPXFPVgy5XpQIjaEEEg\/N1p6rgAelOzTc0ALmkopKAFpKKKACiiigYUA4YH8KM0oQt7CgCWomU7zgZBqWimIjEZPU4+lOCKO1OpMjOM80ALRRULZ3EMc+lADzIB05+lN3M3t9Ka3Cn6U5CSgz1pAAWnYxRRQAUxo8qcde1PzSbqAIouFP1oR8SEHoafTCvzg0AS5FJk0lKBQAlIcg5HUU8AChhnkdRQA9WDDIpki\/xDqOvuKajbW\/2T+hqamBDTJTwPrT2Gxsdj0pGGRikAqPlQT1pck0wnYn0p6EMuRQAAGlAxS0UANYZ5HWkIwM0+igBlFFFAwooooAKKKKACiiigAooooAKKKKACiiigAooooAKVV3cnp\/OlVM8t+VSUCE6UtFFMAooooAKKKazBRz+VAC9KYzk8LwPWmkluv5UUgCiiigYUUUUAIRn6+tSI+eG6\/zplBGaBEpAIwajZSnTlf5UqPztbr296kpgQ0UrJt5HT0pKQwooooAKMGlXBGadQIgEwSQqfu+vpU\/Wopot4yPvfzqGORom2tnHcelAFuikVgwBByDQTQAHrkdRTlYMOKYQQM0g9QfxoAmopqtu46GnUwGsoYc\/nTQ5U4bn3qSmsoYc0AKORkUtQ\/Mh\/wA4NSKwb2PpQA6msgb2PrTqKAISCDg0VKQCMGo2QjpyKQEcv+rNMtydxHtU3BFMRNrkg8elAElFJRQAUUUUDCijPbvShCevFACZpQjHrwKeqhegp1FhDQoXoKdSEgdTimGQn7o\/E0wJKa52qTSRkkHJyQaVxlGHtQBGSx6n8qQjjjg0o5FFIZIjblz370ki7hkdR0pinY2ex61NTEQHlD9KbC+PlP4VI42tnsf51GFw+e1ICXNJk0g5zjtTsUANpcU6igBpHHHWm54zUlMYfkaACJty+460+qy5SQflU2\/59p9KAH0hOKM0nWgBCAc+9Ojb+E9R+tJg4pvow6igCZlDLg1EMjg9RUqkMMimyLkbh1FAEUg3LxTIn2nB6GpaikXByOlAFiio4ydgp2DQApNNPvTsCkJzkYoASiiigYUUUUAFFFFABRRRQAUUUUAFFFFABRRRyTgdaACnqmOT1\/lSqoX3PrTqBBRRRTAKKKKACiio2fPC9PWgBWfHA5P8qZ3yeTRRSAKKKKBhRRRQAUUUd\/rQAU4D1oAxS0CEZQwwaFYghX69j60tIQCMHpQBJUbJjlfxFCsV+Vjn0NSUwIaMgEA8Zpzp3Xr3HrUMqlk45waQE4AHSioIZs\/K557Gp6ACo5ohIM9GHQ08MCMik6mgCCAMrlSMcVKylgMHBByDT9opaACk2jBGOtLRQAzGO\/0NPR88Hr\/OmsMHI\/EUmM0ATUUwPg4b8D60+mAjAEYNQ8ZxnnsanphjU9sUAIr44b86kqEgqcH8DSqSvTp6UgJaKRWDDilpgMZAeRwaZyDg8GpqQgEYNAEVFKUYdORSiP8AvH8BSAb14HJpwjJ+8cewp4AAwBilosAgUL0FLTWcL1PPpTC7HpwKYD2YL1NMLk9OKTFFIAx36miiigY6L7zD8afUaf6z6ipaaEQL936cUtHdh70UhhTom\/hPUfyptIcjBHUUCJmAYEGoRnkHqKmUhgCOhpkq\/wAQ7dfpQBDu2yk1PUMi5GRSwt8pB7dKAJaKbmkoAdmmnmlAJoK8cdaAGlQSD6UmMOWPTFOpsmdhxQBIBS1FC38J\/CpM0ALSMMjj60maOTQAI20+x\/SpaiK8U6NsjB6\/zoAay7T7GkIyMGpSARg1FyDg9RQBG7bSoHbmplYMoIqN13D3pkT7W56GgCxRRSZ9KAG0UUUDCiiigAooooAKKKKACiiigAoopVBY8dPWgBACTgVKqhRxQAAMClpiCiiigAooooAKKQkAZNIjbs8YxQAkvQemeaZUrDKketQjpz1pMBaKKKBhRRRQAUUE4GTTgBjPWgBvfFLtB6j3p2KKBBRRRQAUhNITSUABpVfbwenrSUUATUx0zyOv86arbeD93+VSUwKsseclRz3FSJjCk9SoqR0zyOtMHpjFIBEBG73bNOCgNu74xUbyGNlPUHqKlUhgCDkGgBaKKa7hBk9O9ADqQmgmmk469PWgBetKBimxSK4469xT6AAjIwaQMV4Y5Hr6UtFADhyMilqNThsZ4P8AOpKYCEAjBGRULAx9eV9fSp6QjIwaAIvcH8aer54bg1GymPkcr6elAIYccikBPRUIcp15X+VSggjIORTAWiiigBrOBx1PpTCzH2HtT2UN7H1qPkHB60gADFFFFAwooooAKKKKAFT\/AFg+hqWoV\/1g+hqahCIf4m+tFB++31ooGFFFFACxna2Ox6fWpagIyKljbcvPUdaEIjI2tt7dqRV25qV13L79qiByKAHAZ5pQAKhifaxDdDU9ABSNnHFLSZoAbRRRQMjVcSe1SjBzg9KQjAzUSPtck9+tAifApaKKACmHhsrT6awwc+tAEikMMimyLkZHUU1Ttb2NS0wIRzUcic5HepXG057Gmv8AdOBmkAqAFRg8Yp+Kghfadp6Gp6AGUUUUDCiiigAooooAKKKKACiilVd3J6fzoAFXd9P50y2u4p2aNMq6HBRhgip+grKWGTUj9qV1gKkiPaPm49TQI1qKo216yy\/ZrwCObsf4X+lXqYBUL3CruA5I4qaq4tsnLNz7UAPg8wgs569BUjEKMmkJCLySf61GSScnrQAEljk\/lSqcOPfikpD0460hk9RMMOffmpFO5QfWmyj5c+nNMQyiiikMKKKKAAjIwagjlMR2t0\/lU9MkjDj39aBEwORkdKKqRyNE209PSrW4EZHegAJpKKSgAooooGFFFFABSq236fypKKAJQcjIpGXd7H1qNSVPHT0qUEEZFMRA6BhtbgioUZoXwencVcZQw9\/WoXTd8rcHsaQEhNMkBZCB1pRnHPWkDqHCnqelAD19xS9eDRRQBWliaM70zgc\/SpIpg\/DYDfzqWq00O35l6dxQBZpCajhctHyehxTqAA805Hxw34Gm0UATUVGjY4PTsakpgJULxlDuTp3FT0UAQKwb\/CkGYzlencU6SL+JODTVbPB4NIZMjhxkGnVWcrEDKWCKOpNVFmm1JiIZDBCp+8PvMf6UXEalIQGGDVa0ndpHt58edHzkdGHrUjzGOTDL8vrTAVgV69PWkp4dGO0EHjNNZCvI5HpSASiiigYUUUUAC\/6wfjU1Qr\/rFqahCIT99vrRQ332ooGFFFFABQDtbd270UUATVFINrbux60sRx8h7dKeQCCD0NMRVlXuPxqSNiUGetGMZU9v1pAQpC0gH0YNOxRQAgApaKKACoJVwanprgEe9ADYWyNp6in5FRIuHPtTo3BYj8qAH5J6UY9aWigBrjjPbvTo2\/hPUUUwgqeO3SgCUgEYNRYIOD2qVTuGRTXXIyOopgV5FwcjoakifcuD1FBwy0xRsVietICSiiigYUUUUAFFFFABRRT1THJ6\/wAqBCKv8TflWes39oSOC7JChx5anDN9fQVpsSASBk+lUJ7JrjM8Z8mcdCO\/1oAginWyvHjQyvDsyUGW2GpYkZma402VCrnLxvnGf6GnabNHGPsrp5U45YH+P3z3q0lrAk5mSMLIwwSP8KAIYbaR5vPu\/LZwMKqjhe+ee9XKKSmAtNZgv19KGPHHBqMc8nr3oAOScnrRRRSGFFFFADoj1X05p9RA4YH8KmpoRAOOPTilpZBhwfWkpDCiiigAooooAa6Bxz19aVMhAD1HFLRQAUUUUAFFFFABRRRQAUUUUAFAJU5H4igkKpZiAqjJJ7VDZ3SXsHnRggZIweooEWwQwyKGUMMGoxlTkUTvKsDNBGJJB0UnGaYCEEHBqGdC3zDntirRG5eeD\/KonYpG7EcqCaQEcM+Plc8diasVRjlluLa3njhUmQjeM\/dHrViOQhmU\/dAyKAJiaaaoR6rvjEosrgxddwAPH51NPexRWYulBkRsY29TmnZiuidVC5x3OaWqf9ohCDcWs0CE43sBgfWpLm7eByFtJZVAzuXGKLMdyxRVS0vzdFCtpKsb9HOMUPfjzHWC2lnCHDMmMA+nvRZhdFuonvI4G2Nlj6DqKW3njuYhJGTjOCCMEH0NUCpe5cdy5H61dOKb1InJpaF3+0ov+ecv5D\/Gj+0ov+ecv5D\/ABqBngRtqLjHBygbP60qGGU7Cp3HoQu3+tackexnzy7luC6inJCEhh2PWpHjDc96zLMYvY\/XkH8jWtWc4qLsjSEnJXZk3SYvVa8Ja2\/gx91T\/tf4068KCeE20gWd2CnZg5X1IrSdFdSrAEH1qlJELMZtbVWkc4BA4H19qzsaDQY7AvNNI008nA45PsBT0uZHIhuohC0nKHqD7fWofscsf+kiXzbgcnd0Yeg9KZLPJf26xRQOA5B3t0XB6g0gL0cMkcgbgj2NWaryXSQyRo4IVxxJ\/Dn0qxVCGMndevpTKmprKG9j60gI6KDkHB60UDBfvrU1Qj76\/WpqEIhb\/WH8KKG\/1h+gooGFFFFABRRRQAhz1HUdKmVgygioqVDtbHY\/zoEOkXI3DqKgkGcMOlWqhdcEj+FulACxtuX3HWnVXjJWTH51NuoAdSbqTBNKFoATJNJT6awwc9j1oASoSCjcfhU1Nddw96BkisGUEUtQQsQSO1S5NAh2aaxzQAaUAUANVtrex61NUTDI9xSxt\/Ce3SgBHG057HrTWUsuBUzY2nPTvVeJ8NtPfpQA+iiigYUUUUAFHsOtHJOB1qRVC+59aBCKmOTyajvZzbWskqruKjgUy6utrCCBkM7EAA\/wjnn9KheS8sjvmYXMH8RC4ZffHpQBFMtzE8flXMkty3JjIGzH07Crdnex3OUPyTLw0Z6g1AEneZ7qykidZlAO\/Py49KtWtuIIwCdznJZ8ckk5oAl2ruDbRuAwDjmnUU1+Bn0pgLmkJ\/OgmkoAKYRhvrT6Rhke45pANooooGFFFFAARkYqRDuUGo6dGcEj8aEIdIMocdRzUY5FTVDjBI9KGAUUUUDCiiigAooooAKKKKACiiigAoooJx1oAKKQHPQE\/QU4K57AfWgClq0gW1EO4KZ2Ee4nAA7n8qjtZIYtTkhikRo5UDLtYEBhwf0qxLZG4v1knVXgjTCqefmPU4+lJc6eoaGW0ijSSOQE4GNy9xVLaxHmV0ie7vruNriZY0K4VDjtURnnXR70GVi8EhjD55IBHP61o2tvJFeXcrY2ylSuD6CqF7bvb6XqJkx+9lLrg9iRTQFq\/I2wma7MEOPmCnDOaq2VwHN5DHM80KpuRn6jjkVYuYZ\/tUF1bokpSPYUc4x7imwWt19ouJZtmZYgoCngH0o6B1K8bumn6XsYrulUHB6jmtVlALMBztNURYzfZLCP5d0Eis\/PYZrQYHkeopMaMbT5b4aYiw2qMu07WL89T2on8tdAiEJZ1V1HIwc7uRU9ompW1skCwwnaCAxc0r6fINNECsGkMgdj0BOcmnfUkj1G5mktzFLbNBFIQrSsQQoz6CtKQbYHUdAh\/lUeoQNc2UsKY3MOM1JtYwbSMMUx+OKkor6R\/wAgy3\/3ahRLvT2dYoRcQMxcAHDLnqPerVhC1vZRQyY3KuDioI0vrRTFGiXEefkZnwwHv60+oiezkgliaS3XbuYlwRghu+feqsP\/AB\/H\/rof51Ysbd7eOQysDJK5dtvQE9hVUP5d2zEdHJx+NaU92RU2RZFkkpZlmzz6UqWiQSozTc54GOtOa7hhwI1BzycUouoZFLOoBXkA07z+QrQ+ZVtv+Qgv+838jWrWRbtuvFI45J\/StRXzweDSq7jpbD6QjIwaWisjUpXcEzhYo32RNnzCOv0p6pHHAFQhI0GMnt9as9ap3dl58il3PkqOYx3PvSGVXd9QBihGy2\/icjlvpVyzWaJvKJ8yHHyuTyvsaUtEAkbERluEWqk6mXURBI7LHsyqqcbjSA1aKoW7tb3a25kaRJFJXccspHb6VfqhCEAjBqNgV68j1qWigCEffWpqZsw4I6elPoAhb\/WH6Cihv9YfoKKQwooooAKKKKACgjIxRRQA+Ntwweo60rLuXFR52ncPx+lSjmmIgAy2T1HBoL7XAPQ1JIMHcPxqGVf4h+NICeio4XyNp6ipKACiikz6UAN6celFHU+4ooGIoG88808ACq7ErJmrCtuUEd6BC0UUUAFMPXI4NOzTaAJVbcM1Xmjw3HfpT1O1s9j1qUgHrTAiooopDCgZJwOtABJwKkUADigQKoUVWmuJTObe2VWkUZZnPyrTXvZWZxbW5mWM4ZiwAz6D1qKNbbUSZ43lSTGHRGwT7GgBZlhuFSO4miS7B4MbcqamtJZy7QXMZ3KM+YB8rD\/Gqv2cXMSwRWZt4wQWdxgj6dzWrQBHDDFACIkCAnJx61JRRTAKjkbqo\/GpKikGHB9eKACM5T3HFLkVGOGPvTqQC7qTJoooAKKKKBhRRRQAUZwQfSijqKAJqjkGCG\/A0sZynPUcUrDcpFMRHRSA5FLSGFFFBIHWgAopAc9AT+FOCOewH1oASgkDrThH6sT9OKcEUdAKLCIgc9AT9KcEc9gPrUtJRYBgj9WJ+nFOCKvOB9TQXUd\/yppkPZfzoAfmjJqNCSg\/KnYpgBbHOc\/SkMh7L+dBGQRTQc0gF3Oe+PoKhuLOK5ZDMGbZ0G7g\/UVMh4+hp1ACEGmg0+mdOPSgBy\/dFI3ahOn404jNADBk0U8cU0jqaAEooooGFFFFABUM1rHKdzAg+oqaihNrYTSe5V+wRf35Pzo+wRf35Pzq1RVc8u5PJHsRQ28cHKjn1NScU6MZYk9qkYBhg0tXqykrbDFfs351JUA5XmnK23g9P5UgJaKKKYFK9hcTRXUaFzHkFR1IPpVW4uEu5rdYAzSpJknBG0d81r0yRNw460rDIGVRLv2DzMYDeop\/2mOPAmkRCem5gDVa6nkj2QRAGaToT\/CPWkjsoovvqJpG6u4zk0AaCsGUMpBB6EUtZtsRDqTwwjERTc6jore1aIIIyKYhaKKKAIm\/1h+gpKkZQ3sfWo+QcHrSAKKKKBhRRRQAUUUUAFLGcHafwpKQj06jpQBN1qEjBKHp2+lSo25c0ki7hx1HSmIrYKPx+FT5z0puA2D6UwuVk9hSAmx60uKAcjIooAQrk5oK8YpaKAInXcPcU2FsNjsalbGcimhcMW9aAHZpKKTPOByfagBaQkCnBCepx9KeqhegoAYEZvYVIowAPSlpjOo46n0FMBlABY4H50AFjgfiakZFZChzgjBwcUgFACjAqhZE2t1JZuSVPzxEnqO4p32GaD\/j0uWUdo5PmX\/61Vb6W48tWmt2SWI7kkT5lPsfSgCa9jW1t0jXf9naTMpHUA9vpTYoUu7sT2+6GKNQA6jbvOf5Yq\/byieBJQCAwzg1LQAUUUUwConYtwpwPWhm3cDp\/OkpASKdyg+tJIMofbmkiPUehp9MCE84NLSDjj04paQBRRRQMKKKKACiiigAooooAWM4cj1qWoacJP73HvQIGQ5yuPpSbHPoP1qQEHoaKYDBH6sT+lOCKOgFBdR3z9KbvY9F\/OgCSkqJi\/rj6UoQHk8\/WkA4yKO+fpzSGQ9l\/OjAHaloAYWc\/wAWPpQEDDJJOfWgccelKnTHocUALgUjDj6U6gjIxQA1OpH406owfmB9eDUlABTW6g0uR60jEEYoARPvEevNPqPoQaXcfWgB9MP3qSgUAAJGaXeaMUmBQAbj60ZPqaXFFABRRRQMKKKKACiiigAooooAWP77D2FS1En+s+oqWmhEA4yPc0tB++31opDFViv09KkBBGRUVAJU5H5UCJqKQEMMilpgVLy2d5Engx5seRg9GHpUccs+8iW2EUQBLMzg4q\/Ve+ha4tJI0OGI4pAVFupZSWtrYunTzGO3NWLW585WIUpIpw6HsarreCONUW3l8wDHl7ePzqNJPsavJP8APcTHPlr19hSGaqsG+vpTqpWt0s\/yn5J1GWUgjH51bVt3sfSmIdTWUMKdRTAhIIOD1oqVgGGDURBU4P50gCiiigYUUUUAFFFFAAp2t7HrU1QkZGKfG2Rg9RQhDXG1s9j\/ADqORcjPpVhgGBBqEZ5B6igBsL\/wn8KmqDbiQehqSgB2aTNITigBm6D8TQAUDJ+6M08Rj+LmnUWAYI\/7x\/AU8AAYAxQSAMk4phk\/ujPuaYElMMg\/h5phy33jmgDPSlcAJZup\/AUAegpwWloAcAAMCloopgFFFFABRRRQAVEzbuB0\/nQ7ZJXsOtJSAKKKKBgpw49+KmqA9M+nNTA5GaEIjcYf6ikp8o+XPoaZQAUUUUDCiiigAooooAKKKKACilAORTqBEZUdcUqgBvwpxGQRTD0zQA\/IzilpAAKWgBrdQKE+7j04ofsaROGI9eaAH0UUm4UANP3j70Lw59xQTkikNAD8j1o3CmUYoADz+eaKWigBKWiigAooooGFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFAAv8ArFqaof4l+tTUIRC3+sP0FFLJ98e4pKACiiigYAkHIqRWDDio6OhyOtAiaimq272PpTqYDJldomEZAfHykjjNU4bUWuZZX3SnlpW6Cr9RXFvHcoElBKg5wDjNIDOaWe8Y\/ZAEjXrIeC2Ow9qtwSuwHnII3zhfmzupl3MtqVjRd8h\/1ca\/56VV+S3kWa7Yy3LfdRRnb9BSGacDyOn75BG2TgA54qWqEF4skgjkjkic8rvHWp4byKadoUJZkHLAcfnTuIsUhAIwaWimBCQVPPT1oqUgEYNRspX6etIBKKKKBhRRRQAUnIIYdRS0UASggjI6UyRf4h26\/SkjOG29jyKlpiIMinBWbtge9PVFXoMU6lYBixqOep9TT6azqvU8+lMLsenyj9aYEjMF6nFMMhP3Rj3NMXG\/B6460p4zntSATGTknJ96WlAp2KAGNwOlMhfB2nvUpGRiq7rtNAFmimI+5cnrS5oAlooopgFFFISFGTQAtFQsxPzdMc4qWgCNxh\/qKSnSjgH0NNpAFFFFAwp8R+XHpxTKVDh8eooESEZBHrUI6VPULDDn35oYBRRRQMKKKKACjuKKD0oAdgUtHWigQUUZpNwoAWmdz9aXd7UnU5oASnIfl+nFJSd6AHMQQRmm980UtACUUtFABRRRQMKKKKACiiigAooooAKKKKACiiigAooooAKKKKACikyPWlG49FP8qACilCMepA\/WlEQ7kmgQ0kDqaTOegJ\/CpQijoBTqLAQhXP8ADj6mneW3dgPoKkpCQOpFADRGAckk0+mGRR3\/AEp9MCOTqp\/Cm06Xqv1ptIAooooGFFFFAB\/Ono+eD1\/nTKKBE1FRq\/ZvzqSmAmATnAyOM1lxlLe8uGuCFdmyjN0K+xrVqOeGOeMxyruU9RnFIDKlZtQcCPKW8ZJMp4J+lT21xZRAQQyqB\/M\/Wkvow91b2v3IWBOBwCR0FF95ENo0TIoJGEQDnPtSGaCtztbr2PrT6pRSCGCGO4cByAvJ5Jq0rYOG\/A0xD6SlopgRMu3nt\/KkqaomXbyOnp6UgEooyKUKzew96BiE4pQjHr8o\/WnqgXp19adRYQ1VC9BTqazgcdT6CoyWbqcD0FMB7OBx1PoKYWZupwPQUdKKQAAB0oAz0opkT\/Ng9+lADxGN248mkmzt6fjUlIQCMGgCKJ9qHPbpUqkMMjpURTCEDrnmkhfadp6GgCemSLuHvT6awxz+dADUG1QKWimo\/wC8IPfpQBYooprPjgcmmAMwX6+lRnJOT1o9z1oxmkAU+M5Qe3FMp0R5YfjQA5xlSKiByAanqHGCR6GhgFFFFAwpCcYPoaWigCao5R0b8KWM5Qeo4pWG5SKYiOikByKWkMKKKKACiiigABIGKMn1oAycU7AoEMpQppQvGDzTqAG7eOvNIOmafTOhIoAKKKKBhRQelHbigQUUUUDCiiigAooooAKKKKACiiigAopMilCsegx9aACinCP1Y\/hThGo7Z+tFhEWQenP0pQrH+HH1NTUUWAjEZ7tj6ClEa98n6mnFgOpAppkXtk\/QUwHAAdABS1EZG7AD6mkyx6t+VK4EpOBk0m4dqi2jvz9achygoAduPpRk+tFFACY9cmlwB2opr5xQA1+\/51NUDD5T9KnHShAMl\/h+tNp0vQfWm0AFFFFAwooooAKKKKAClV9vB6fypKKAJaWolJXp09KkBBGRTEMmgjnj2SoGX3qjPBBY7TBbtJO5wmcnn69q0qKAMtLAZMl4TJK3cEgL9MVZhURRiIuzjsznJ+lWmUMMGq8kZTtkUrDJVbHB6etSVVV+xOR2NSq23g9P5UCJaKKKYDQig5A5p1FNcEj5TigAZwv19KYWZvYe1NGKWkAAYoAzR2pkL87T36UAShaWiigBrDBz+dQSLg5HQ1Ox6imkZGDQAsT7156jrT6rAmN\/pVgEEZHSgBGHcdqiZV3jJ4apicVDKO46UATjpRUcT5XB6inZoATGDjt2qJ12txUtIw3DFAEjNjgdfWmAVnNdTwvGZLmGQs4VolA+XPoavXDGO3ldeqoSPyoAY13bJJsedAw7Z6VPVWxgjOnRxlQRImW9yRzTXMlstvZQPl2B+dxnCj2oAtnrSrw49+KqRTTxzvbzsrsI\/MRwMZHoRVb7VerYx3pkQrwTHt6jOOtAGzUTjD59RVQS3UF1AJpEdJyRtC42HGfxpge9uTP5UscYilZFyuc\/WmBalmSHyw+cyNtGPWn96zpbg3MNjKV2sZwGHoRkGllvXkuJUjuYrdYjt+cZLHv+FIDQoqkL2VrSC7AHl5xKoHQZxkfSprOaSdXmbiNj+6GOcev40AWYzhiPxqSoQcMD+FTUICEjDEfjRTpBhgfwptABRRRQMKKKKAEzgg+9SUw8jFOU5UGgQtFFFABTW6g\/hTqRhkEUANoNA5FFAxcCigdKKBCe1FB60UAFFFFAwopAc9AT9KcEY+goASkyOg5PtUgjHfJ+tOAA6DFFhEYVj2x9aURjuSakppdR1NMBQAvQAUtRmQ9l\/Omkk9W\/LikBIWVepApvmjsCf0pnA6CjJouA4ux9B+tNJJ6sTSHAIBYAnoCaGKqyqzAFjgD1oAOB0FGaD1opDClFJQKAHUR9CPQ0U1GQyOFYErwwHamIlopNwpN3tQA6imbjSb1LmMPlwMlfagB0v3DUg6CoWHBqZfuj6UANl+6PqKZT5fufiKZQAUUZA6sPzpC6Lt3OBuOB7mgBaKKXBoGJRRRQAUUUUAFAyDkUUqqW9hQA9WDCnUgAAwKWmIKQjIwaWigCvJEVyV5HcU1Xx1+76+lWqieIHleD\/OkAK236fyqQHIyKrAlOD0\/lUitt5HIPagCaikBBGRS0wGMmeRwaZ7HrU1NZQ319aQEdROu1sjipeQcHrSMNwxQA5G3KD+dOqvG2x8HoeDU+aABhkcdRTetLmkoAZIuRkdRSxZCU8L60MvHA5HSgBKCMjBoAzTtooAjRdvHcmnhec5qKUkED071Kjb1z+dAAwwc\/nSU+mdDigDLFrctBFELSOPy3Ulgwy2DWo6h1ZG6MMGgcjk0tAFGMX1vCLdIUkCjaku\/HHbIpz2s0Yt5Ym82WEEMGON4PXmrmcd6TPpQBVihnlne5nQRny\/LRA2cd8k1G1pMdGW22jzQAMZ96vZNH40AQ3EMkstoyjIifLc9BiqttJco14IIFlU3DdXwQcD9K0oz8zD8aIoY4t\/lrjexduepNMDPFlKkFqgwzJLvc\/nmh7eSC4leO2juElO7kgFT369qvdGYe+aKQFWaGaaKKDy1jjY5l2ngD0H1p9nFJAHhYZiU\/umz29PwqeigAIyCKkU7lBqOnRHqPQ0IB0gyh9etRjkZqaoAMEj0NDAWiiigYUUUUAFKh5I\/Gko6MD+FAh9FFMye9AD6KYpwMelLuNACdCRRRnuaQHPQE\/SgBeaM+lKEY+g\/WnCMdyTQBGT6n8BSgMei\/nxUoUDoAKWiwEYjPdvypRGo7ZPvTiQOpAppkHYE0wH0VEZGPoP1phOepJ+tK4ExkUd8n2phkPYY+tM+lFFwFJJ6sTSdOgxRRSGGaKKKAClXrSUq9aAMuIWoZ479QJ2Y5d+jDtg1JcJJB9hUMZnVyATxng4qQ3DiMxXdrJI\/I+RNytUcVvNGlgrqxKOxbHO0YOM0xE8NzL57wXESrIE3qVOQRTbe9kkgNzLCscAQtndkk\/SllRzqe8KxX7ORnHGc9Kjhtnl0QW5BRymMEY5zQA9LuZWiM8CpHKcKQ2SpPTNOW9H2aeWRArQkqR7jpVe3it2kjU2MyyggknO1SO+c0l3AzaksS\/wCruMO4\/wB3\/IoAstdzFooooVMzJvYMcBRUVnI4mvpGiO8FcovOTjtT5y9tfi4MbyRvHsOwZIOc9KhAuWS\/eON43cqUyME8UAWEuZ0liW5hRFlOFKtkg+hp0NxNNcOiQrsjkKM5b+VUhGrzWrQ2s6hZBveQHP8An3q9YIyPdblK7pyRkYyMDmgCJL2eRTNHbK8IYrgNl8A4zims0y6tN5EauxiX7xwByahlVySY7aaG83dYwdh56k9MVciRxqkzsp2mJRuxwTmgAju3ktJZDATLExQxqc5I\/wD10sV1OtxHDcxIvmg7CjZ5HY1WZLhbS+8tXDNOSMDBK8Zx+GabFGrXlo8NtMiKW3O4Pp70ATNe3MkcrxW6FInZTluTg9qtQyLNFHKvRwCKr2kbraXQZGBMrkAjqKfp6sljArqVYKMgjBFAFJGsRdXX2ry9\/m8bh2wKlmEA+xG3CiNp8jaODwamtIyJbounWXIJHUYFF4jNJabEJCygnA6DBoAWae6Dv5NupRO7tjd9Kr3V1NLFZywABZJF4LY554PtTZFJupxPBNKzH91jOwDH5CkCSR6ZZExOTFKGZQp3Ac9vxoAtS3M4nW3iijMnl723NgDtgVNBI0sIaSMxvnBU1VvCjyKZrWRkKgo8YO5T6HHIqaxEotQJt2dx27\/vbe2fegCejvgcmhQW6dPWpFUKOKAEVMctyafRTWYKOaYC0xnzwv500kt16elGKQDlfHDdPWpKiwcUKxXpyPSgCWikBBGRS0wGOgYe9Q8xnBHHpVmmsoYYNAEanHK\/\/rqVWDDiq5DRn1FOU\/xKaQE9FNVg319KdTARlDDBqIgqcH86mpCARg0AV3TLA\/nT84pWBU89PWo5fuUgJAvrTqjhfcNp6ipKACiiigAooooAbIm9cd+1QRtsbnp3qzUUyfxD8aAJaRhkcdRUcLHbg9ulPLepxQAnIo\/GiigAooooGFFFFAAOHU\/hU1QN0+nNTA5GaEIjkGHB9eKSnyj5M+nNMoAKKKKBhQpw49+KKQ9PpzQBPUUgw4PqKkByM02UfJn05piGUUUUhhRRR060AFIRkYoBz0BNOCMfQfrQAmSaTI\/GpBGO5JpwAHQAUWERAMei\/nThGe7flUlISB1IFACCNR2596dTDIOwJppkY+g\/WmBLTS6jv+VQk56kn60UrgSGQ9l\/OmFmPVvypKKADj0ooopDCiiigAooooAKKKKACiiigAooooAXJoyfWkooAUEk4p2D3NRk7SD6VLTEAqGO3VJ3nZ2d2GBn+EegqaigBGyBkdqQk7etOpg9D2oAehyopaZF0I9KfQAtGaTI9aTcKAFb7ppYzmNT7Uzd7U+P7goAJDhCajzT5f8AVt9KZQwFyaMmkooGLk0ZNJQAWOB+dAg3GnBCeW\/KnKgX3PrTqACikJAGTUbMW9hTAcz9l5NRsccnmlprjK0gJcCikUEKAaWgApnTin01uoP4UAICQcipFYMOKjo6HI60ATUU1W3ex9KdTAQgEYNQMpjbI6H9asUhAIwaAIQc8g1Ij54PWonQody9KUEMOP8A9VICeimI+eD1\/nT6YCEZGDUbrt91\/lUtFAFIgo\/H4VZRgygimyxccf8A6qiifa2OxpAWKKazYBNEb71z370AOoozTS1ADs00t+FAVm9vc08IB7mgCJEOMKMD1NSLGBz1PqafTWYL1NMCOiiikMKKKKACiiigAp8R+THpxTKWM4Yj15oESEZBFQr0qeoSMOw\/GhgFFFFAwoopM56c\/SgCSI\/Lj04px5FMjVgSSMA1JTEQA44PUcUoDHov51LS0rARiM92\/KnBFHb86UkDqcU0yDsCaYD6KiMjH0H60w89ST9aVwJi6jv+VNMh7L+dR\/Tiii4DizHq35U3j0oopDCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigBCMjFOBO0fSkooEBzjjk1JUanD49RUmRTAKY3DZ9aduprHPWgBBwxpaQc9Mn6U8Ix7AfWgBtFPEXqx\/ClEajtn60ARZH1+lTR8IKXGKWmA2T7jfSo6lPQ1CvKj6UmAtFHU4HJp6pjk8mgBqoW68CpAABgUtJTAWms4Xjqaazk8L09abjFIAOScmiiigYUj\/dNLRQAsZyop1RJkAj3paBD9wppORigAkZpdtACUUu3ikIIFAB\/OpEbPB61HRQBNRTEfPB6\/wA6fTAQjNQyRlTuWp6KAK6sGHv3FSK\/ZvzpkkeDuWkVt3B6+lICxRUavjg9PWpKYBULxfOHUfUVNRQBARuXFMiyrHPHFTsmTkHBoVAvPU+ppANCs3t7mnhAPc06mswXqaYDqazBepphdj0+UfrTQMc9\/WlcBxdj0+UfrTQO\/f1paKBgORRSDqaWgAHJxTttNH3hT6BCYFLRRQAw\/eP50A4dT+FD\/eB\/CkPSgCeo5eGB\/Cng5APrQQD1GaYEIOegJ+lOCMeuBUtFKwDBGvfn607pSGRR3yfamGRj0GPrTAloqE5PViafGcoPUcUAK7FVyBUZZj1b8qlIyCPWq46UmAvHp+dGaKKQwooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAoopM0ALRShGPbH1pwjHdifpTER9xTwrHt+dSBQvRQKMkEc8UANEfqfypwRR2pSQOppvmL2OfoKYD6KiMjHoAPrSEserH8OKVwJSwHUgU3zF7ZP4VGAB2paLgO8xuy\/maWNi2c9jTKdH95h+NAElQohIx0AqaimAgAUYFLRTGfsvX1oAVmC9ajJLdenpR79TRSAKKKKBhRSqNwzS4FAhtLg06igBh4OKKVuxpKAFjOV+lOqNDtY07d7UAOpCMgim7zR8x9aACinCM92\/KlEajt+dADKej54PX+dNdQpGOhpKAJqKjR+zfgakpgFQyRfxLU1FAFdWzwev86erbeD0\/lRJFnlaYrdm6\/zpDJ6WolYr9PSpAQRkUxC0UUUARM7EkD5f500Dv39alZd3saj9jwaQBRRRQMKKKKAD3opzDIIpgOQDQIU9M+lPplOX7ooAWikyPWk3UAD9AfQ0lBORiigB8R+XHpxT6ijOHI9RUtMCIuxJAAGPWkOT1JNKww59+aSkAdKKKKBhSxnDEevNJRnDA+9AiaoHGHPvzU9RzDgN6U2BHRRRUjCiiigAooooAKKKKACiiigAooooAKKKKACiiigAoAJopV70CDBoC+9LRkUwA8im0pakGT0BNABRThGx6kCnCJR1yfrRYCLP4\/SnBHPbH1qUYHQflS80WAYIh\/ESaU4TGABk4pfrUUrYGB65FAEuKWmBiwB6ZpcUALu9KQ5Ix0oozQBGBnk8n3paDwx\/OigYUUUUAFFFOVCeW6elADQC3T86kVQo4pelLTEFISAMmms4HA5NM5JyeTQArMW9hSAelCkFivcU+kA0L60uBjFLRQAweh6iilcd\/wA6SgYkR6rUlRfdkBp+4noKBCkgUhY9qArN6UvlDuSaAGE+ppeT0UmpAqjoBTqLARCNjycCnCNe+TT6aWVepFMBQoHYUtRmT0Un68UhZz3x9KAJOlNMi9jn6VHgHrz9aWlcALFjyMAUUUUDCnK+OG\/A02igCaio1bbwenrUlMQVFJEGGR1qWigCsrYO1vzp4JU8dPSnSRhx71ErFTtb86QywCCMilqEEqcj8RUoIYZFMQtNZQw9\/WnUUAQ8g4PWipGUMOajIIODSAKKKKBjsimDgkUtFAgpKWigYlLiiigQUUUUDDOCD71NUBGQRUqHKg0IQ2UdD74ptSOMoRUYORmgAooooGFBGRiiigCRDuQGhhuUj1psR5I\/GpKYiuOlFKww5H40lSMKKKKACiiigAooooAKKKKACiiigAooooAKKM56c\/SlCOewH1oASkzjvUoiHck04BVHAAp2ERBWPRfxNOER7t+VSZNICQTmgBAiLzj8TTs+lRyONpA6inBiwBxjNMBcnPPekkZQpyRnHFIwLAjNMwNvA6ikBIGyAQOtGTUcJ4I9KkoAKZKMr9KfQeRigCOE8EVJUK\/LJj8KmoARhkEUxT8\/1FOLc4pmPmz7UAOJzRRRQAUDJOBSqpbrwKkAAGBQAioF56mnUU1mC\/X0pgKTjrUbOTwvA9aQkt1\/KikAdKKKKBkb5VgwqYEMAR3phGQRTYWwSh\/CgRNRRRQAUzoSKfTWHGR1FACKoZue1SbQKjHXINLGxZR0zQBJkdqQsB14+tJz60hUGgBTIvbJ+lKrbhmm4GMUkZw2D3\/nQBIwypFQrjHTFT1Eww59DzQwEooooGFFFFABRRRQAUUUUAFKrbeD0\/lSUUATUVErbfp\/KpAcjIpiFpjoHHvT6KAKwJQ4bp\/KngkHIqR0DD3qDmM4PT+VICwrBhxS1CDg5BqRWDD+lMB1IwDDBpaKAISCpwfwNFSkAjBqIgqcHp2NIB20Uj8YPvTqRhuUigBtFIpyAaWgYUUUUAFFFFABTojwR6Gm0qHD\/UUCJahxgkehqaopBh8+ooYCUUUUDCiiigABw4P4VNUBGRUyncoPrQhEcw6N+FMqZxuQioRyKGAUUUUhhRRRQAUUUUAFFABPQE04RMepA+lMBtA56An6VKI1HbP1p1FhEQjY9cCnCJR1yfrUlNLKvUimAoAHQUZqN5MqcKfXmlByAc9aAHFscnGO9MdwykKCaXApaQACSBniiiigBMDOcUtFFACEgU2nMM9OtIQQKAIx8sv1qaoZBkA1Kp3KDQAtGaQkCmCgBso5yKfk0hGQRS0AFFFKqlvYetACdTgDJp6pjk8mnABRgUtMAopCQoyajZi3sPSgBWfsv503+dHSkJAxnoTikAtKBTgMUUAMYYPtRTiMjFN+vagAqOQYIYVJQRkYNAxUbcoNOqCM7HKnoanoEFFFFADCMH2NNjO1itSMMimY5zjtQA\/IzjNLUTdvrTtxNAD6Y3ByO3NJ1paAJQcjIpsgyuR1HNJEeq+lSUwIaKMbSV9KKQwooooAKKKKACiiigAooooAKVSVPt6UlFAEoIIyKWoQSpyPxFSghhkUxC01lDDBp1FAFYgxnHang9CDUrKGGDVdlaM+opATq276+lOqAHOCp\/GpVbdweDQA6kIBGDS0UwGUUUmRSAYOGYehpaG+8CPpRQAUUUUDCiiigApDxg+hzS0UATUyUfLn0NLGcoPXpSkZBHrTERUUi9KWkMKKKKACnRHqPQ02hThx78UCJqrkYYirFRSjBDfhQwGUUoVj0GPrThF\/eb8qAGZxQAx6KfxqZUVegFOosBEIj\/E35U8RqO3PvTqYZFHfP0pgPoqIyMegx9aQ5PUk0rgSF1HU8+lNMhPRfzpoAHSigAJY9WP4cUgAHQUtFAwIyMUkJ+Uj0paZ9yUHsaBE1FFFABRRRQAUUUhIoAWikU5GaMigBhHBX8qSMkL+NOPJzSAYzQAp5NFFFABRQAW6fnUiqF+vrQA1U7t+VSUUlMBaazgcdTTWfPC9PWm0gA5JyeaUCkPTimwt1Q9RQBIQCMVG43KR3FS01hj5vzoASF9y4PUU+oD+7kDDpU4ORkdKACmuO\/506igBlFGMHH5UUDGSLkZ7ipI33r7jrSUxAVkI9qBE1Jmm0UALmkoAzQRjFAAmGGafUUfyuV9aloATApccYopC2DigBoO0g\/gamqE8596fGcrz1HBoQCSjo34Gm1KRkEHvUI9D1FAC0UUUDCiilwfSgBKKDwabnEg\/KgQ\/Bo2+9OooATApp4JFPprdRQAlAJU5FFFAyVWDDIpahGQcjrUisGH9KYh1IQCMGlooArshjORyDSghhkf\/AKqmIyMGoHQody9KQEqPng9f50+oAQw4\/wD1VIj54PX+dADMmiiigBKWlwaQcigAooooGFFFFABRRRQA6I8sPxqSoVOHB9eKmpoRCRh2H40U6QfMD+FNpAFFFFAwpD0paKAJQcgGlpkR+XHpxT6YgpOlRszFiAcYpuM9efrSuBIZF7c\/Sml2PTApKKAEIz1JP1paKKBhRRRQAUUUUAFFFFABTZBlfpTqKAFRtyg96dUUR2sVP1qWgQhOKQtQ2CMUlABmiiigBqngj3p1IBjPvS0AFFFABbp+dABTlQnluB6U5UA56n1p1ACdKWimM\/ZfzpgKzBetRkluv5UYz7mnBfWkA2ijoSPyooGFRyAqwcVITgZpwAx60CAEEA+tBNBGRim+x6igBGXK4pIW\/gP4U6o5AVYOKAJ6KaGBUH1oJoAG5+opKKKACijBNBGBQMAM04AVEjbZCp6E1NQIKRhkYpaKAIW4IYVJupGHP15ooANxooozQAUqHD\/Wk59KQ9OOtAE9ROMPnsakU7gD60jjcp9e1MCOigHIzTXOMfWkMdtO4HsKfQOaKBDW6Z9KY44zUvUVHjIwaAHbuOlJuNFFAByaKKKACiignHWgYUdDkdaaW\/ujNIrHODQFidW3fX0p1Q+461IjbuD1oEOpOtLRTAgkjKnctIrBh6EVPUMke07lpAP20tFFABUfRiPxpxbkj0pp65oAWiilUZGaBiUuDTsUUCGd8UUNwwPrxRQMRun05qYHIzUVPiPyY9OKEIJBlD7c0ypahHHHpxQwFooooGFFFFACocP9RUtQHjn05qahCI5BhwfWkp8gymfTmmUAFFFFAwooooAKKKO+KACinbaNvNAhvfFLt496cBiigBoX1pBT6Y3DfWgBjDDqw9afk0hGRiloAKKKKACiigc9BmgYUDk4AzThH\/eP4CngADAosIaI\/wC9z7U+ikpgLRUZf+7+dORtw56igBJM7c9u9Rsdq59O1TVCV6oaQDxgjI6UtRQtjKHqOlS0AIwyOOo6U0c0+mEYb2NACEZBFELZG09RS1G\/yMHFAE9NYY+b86XcCMikJzQAlIRkYNLRQMRRgAUtBGBmigApAcSYPQiloMe4g5xigQ+kIyMUtFAEEi5Ge461JE+9eeo60MOfY1EP3cme1AFijpRSEigBG5pO9FHvQAYFLRQTjrQAUnQ0oy33R+JpRGOrc0AER6jtUlJwKWmBCRtYjseRSMMrUko+XPpTKQBG3yc9qXd7U3G0U7bQAm40U7aKXFADKKVvvfWmlgKBi0hIHWmFyenFNpDsSBtxwOKXaO\/NQ5wwNTjkZpgFG0ZzijNJuFAhTwR+VH86jY88U5GzwetAWJkbdwetOqGno2eD1oEPooopgQHJHvUlIMYBFLSATaM5pH4XPpzTqQkd6AG0qdxTV6Y9KUcMPegB9FFFADZPu59OaSn1GvTHpxQAtLGcOR680lGcMD70ATVE4w59xmpajlHAPoaYDaKKKQwooooAKfEfkx6cUyljOHI9aBElQjjI9OKnqJxh\/qKGAlFFFAwooooAKRs446ilooAeDkZFFMjOAV9DTt1AhaM03JpKAHbqaTmiigAoozShWPt9aBiUAE9BUgQD3PvTqLCGCMfxHNOAx0paSmAtFMMg7c0wkt1P4UAPMg7c0wkt1oopAFCnawPY8Gig8igZNUcg43elLGcrz1HFOpiK8gwQ46ipVIYAjoaaRjKn\/IpkR2sUPfpSAmpCMjFGaQmgBByKQjIwaWigYg4AFLRQBmgBN2GUHoakpuwHGecU6gQUzpx6U+muO\/50ANVsSYPcVJUMg4yOoqVTuUGgBaKKTIoAGGQRUbLuX3p+6koARPuDntS0UmecDk+goGLSEgdacEY9eBT1RV6D8aBDFVyOmPc05YwOTyfU0+mBsgnPemA+mOflO3rRn2\/Oj60ABIIIHeiNiwOeo60U0HbJ7GkBJUQGMr6VNUcgwQ34GgCN+gFSLyozUb\/dp8Zyg9uKAHUUmR60bhQAN0+lQsMGpC2eMU1xkUDRHRTtp9KXYfWkVcYelPBwoGaXYB1NAC9hn9aBDc+nNKA3pTwrHouPrThGe7fkKYrkWw9zQFUd8n2qYRr3GfrTgAOgosFyIBj0U\/jSiNj1IH0qWiiwhKWkzikDqTgEE0wGKcDBo3UlFIAyaKKKACkPTPpzS5ooGPpMim0UCF3U0dT70tFABSEZBpaKBkqnKg+tI4ypFNiPBHoakpiIAcjNLRjDEe9FIYUUUUAFGcEH0NFB5GKAJqZKPlz6c0sZyg9elKRkYpiIqKQdMenFLSGFFFFABRRRQAd6KKKACigZPQZpwj\/vH8qBDfYcmnBCepxTwAOgpaLANChegp1FNLBetMB1ISB1NMLk9OKb3yeTSuA4yf3R+JppyepzRRQAUUUUDCiiigAooooAFO1gex4NTVCeRinxtleeo4NCEJION3pTCMsD6VNUWNpK+lABRRRQMKKUA0oAoEN7ZpqttlIPQ1KRkYNQyLkH1FAE1FNjbcgPfvTqACiijNAEeMZWkjJXI96e3JHtSUAGSaKOlAy33R+JoAKQHP3RmniMfxHP8qf0osAwRk\/eP4CngBRgDFLTS4HHU+gpgOprMF6mm5Zv9kU1lAGR260AK0jEHaMfWiPlRSU2M7SRSAlopu40mSaAH5ApjYYcUmKWgCRDuUGlYblI9ajjOGI9alpgQdVIPWkXpT3G1s9jSAE9FP40gEoxTgjHqQKURDuSaAGcDqaBk9AT+FShVHQAU6iwEQRz6ClEfqx\/CpKKYDRGo7fnS0hdR1IpvmjsCaAJKKiLufQfrScnqxNK4EpYDqQKaZF7ZP0qMADtTWbaw9qAJd7HooH1pCGPV\/yFOooAYU\/H60MMAH8\/an0UAMoo70uMUAJz6UY9TS0UAGBjFIKWk7mgAooooGFFFFABRRRQAqHD\/UVLUB4IPoanoQiKQYcH1FJT5R8ufTmmUAFFFFAwooooAdGcMR+NSVCDhgfwqahCImGHPvzSU6UcA+hptABRRRQMKKUIx68U8IB25oEMCsegx9acIwOvNPopgFFITjrTTJ\/dGaAH0xnAyAefamEk9TQB6UrgOVtyg+tBGRikRdufenUAMHv1ooYYbPrRQMKKKKACiiigAooooAKKKKAChTtbPY8GigjIxQBNUcg43elLG2V56jg0+mIgJwCachBUEd6TGCV9P5U2I7XKHv0pAS0UUUAFNcd\/zpScUgYMD+WKAI0OyTHY1Lmoyucexp1AC5pKTPOOp9BTgjHr8o\/WgBCQOtAVm6DA96kVFXoOfWnUWAYsYHJ5PvT6QkAZJphk\/ujPvTAkphkA4HJ9qYct9459qUL+FIBNxdtpOOM4FOAA6CoWyrhxUwIYAjvQAtFFFADMYOPypMc5p7DI96aORQAUUUUDCijBpdpoAafUdRzUwOQCO9M20RH+E9RQIkoopjuQ2AB+NMB9JURZu7flRs9efrzSAeZFHfP0pDIey\/nQFFIwwM0ABZz3A+lJtJ65P1ppbDipaAGhcegpCcZ9qfTGHP1FABGdy570pXJzUcZ2vg9+KmoAQgYxUTjK+4qamMOfrQAkLZXHpUlQKdkn6VLuoAdTWJBx60hJooATt706kIwxHvQM0ALRmk+tFABk0UUUDCiiigAoooPGPegAopQCaUCgQ0jIxUkZygNRjjj0p0R+8PxoQDyMgioV6fpU9QsMOR+NDAKKKKBhRRRQAEZGKkQ7lBqOnRH7w96EIewypHrUK5IxjJqeimBGIz\/EfwFPAA6ClooAKSk3ckelJmgBxNNzRRSAjxzg846Uo5OKVx39OtNJ2sD26GgB4ApaKKACiiigBGGRimjmn0w9TjpQAUUUUDCiiigAooooAKKKKACiiigAU7Wz2PBqaoSMjFSRtuXnqODQhDZBxu9KikHGR1FWahxglf8AOKAFRtyg06oo8qWFOJ9aAFJyMU0DGfc5pRlvuj8acI\/7xz7dqAGA5+6M08Rk\/eP4CngY6UtFgEChRwMUtNZ1XqefSmF2PT5R+tMCRmC9TiozIT90Y9zSAc+ppO3FIBdpJyefrTgMUyF9y47ipKAEAAGBS0UUAMkUH8aZC2DsP4VMRkYNQSAgg9xQBPRTUbcoNOoAKYRg+xp9NYgjFACHgUsZyo9aSmodpYUAS0Uituz7UtABTG+V91OyPWmsc0ATUyQZXI6iiI\/Lj0p9MCs54FSqcqDUbjGR6dKWE8EUgJKCMjFFFAELjK59KkQ5UGhlyD70yE8kfjQBLTWxj3pxGRioxQA2QcgipUbcoNMYZBFJESMigCWkYZFJknpSgetADNuSDTwtLRQAjDI4600cin01hg57HrQAsgw2fUU2nyDgH0NM7UAFFGDS4AoAT8KM0tFACUUD0ooGKnUj8adUZ459KkoEFFFFADW+9796RThx78Urjv6U08DPpzQBPUco5U\/hTxyKbL9w0wGUUUd8Dk0hhR14AzThHn7x\/AU8AAcUWEMEf94\/gKeAAMClopgFFMLgcDk0xizd8fSgCamS5C5B470qNuUGlIyMGgCAnawPbvUtRHgc9uD9Keh4weo4pAOooooAKjxkFT9KkpjYzn8KAFjJK89RwadUY+WQejVJQAUUmRSZoAVhke9NoooAKKKKBhRRRQAUUUUAFFFFABRRRQAUqna3seKSkOOlAE9MkHG4dqVc7Rng06mIhCs3QYHqaeIwOTyfen0h460ALRUZkH8Iz\/KmnLfeP4CgB5kA4HJ9qYWZupwPQUdKRuFJ9qQCgegpQvrQjBlBp1ABSYApaKAIHHlSbh0NTg5GRTJAGGD+BpsLdUPbpQBLRRRQAU1xxn8\/pTqQmgCGM7H2noamzTNvIPpTsE0AGaSlC8elIASKACjFFFAxq5UtTuTRRQIMUIAwzS7SaEXaMZoARTtYH8DU1QsOfrUkZyvPUcGgBso4z6daiQ7X\/SrBGRiqzDDEUAT0UinKg0tABUWNsvFSEgU08nNABuNFFFABTWwqk06opTnHpQMlifcvuOtPqqjFGyKsggkgdqBC0UhIHWmpIGBJ4waAH0UUUAOYZUiohyKmqHoSPehgA6UtIOtLQAUUYOfanUAMP3se1FK\/Y\/hSUAFKh4K+lJR3zQA+kJptFAC0lFHfA5NACqxUYxkUhYscfoKcEJ6nHsKeAAOBQAwIT9449hTwABgClopgFFNZwPc+lMLE+w9qAHs4HHU0wkt1PHoKSikAUUUUDFjOHI7GpagPqOo5qYHIBHehCI5Bhgex4NMXII56fKf6VMy7lIqEYzz34P1oAlzTc0lFABQRxS0lACMMr70uc80Ud8UAFFOC0tADQvrQw+XinUUAMooxgkUUDCiiigAooooAKKKTPOByfagBaTPOOp9KcIyfvHHsKkChRwMUCIwhPXgU9VC9BTqQkKMk4pgLRUZkJ+6Me5pY2yMHqKAFckKSvWovvck5qeoWG1sdj0pMAo69KDSjmgBMetGPXmloPIoAjjOx9p6GpqhkGVz3FSRvvX370AOooooAQjIxUL5BDjr3qYnFNYA596AHKwZQaCajQbVx704cnFAC5pKcBihhke9AEbNtZafuG7b3ppG4D65prYEik0ATUUUUAMP3qKVuo9aSgBAcOAe9SVC\/GCKlByAaAFooooARumfTmkQ4f2NOqPHGPSgCeoZ16N+BqRTuUGlYblI9aYEMR4Ip+4VGgwOetLSAUnNFA5OM804DFACYNAHrTqKACo3UH6H9DUlBGRg0AVQMNz2606MnzM+vWnlcgg9fWkiXGSevSgBZW+XHrUbfKoX8TUxAPUVFKuDn1oAkhfI2nqP5VJVaMEuMdqnoAlqJxh\/qKlpkg4B96YDB9760+mHpTxyM0gCikyBSbqAFYZUj1pgORS5zQKACiijvgcmgYUd8Dk04IT94\/gKeAAOBQIYEJ+8cewp4AA4FLRTAKKazAfX0phZj7CgB7OB9fSmFmb2HtSUdCDSABSr0z3pcDOaWgBjcNn1opxGRimjkUAFFFFAwp0RxlfxFNpM7SG9KBE9QyLhj7\/zqamyLleOo5FMCPNFAOce9OC0gG0oX1p1FADWGMGmPkDI6jmpSMjFMHoeooAeDkZFFMjOCV9OlPoAKKKYc7iD+FACtzjHUUlFFABRRQOeAM0DCkzk4AyfaniPP3j+Ap4AAwBiiwhgjJ+8fwFPAAGAMUtNZgvU0wHUhYL1NRl2PTgfrSY796VwFLk9BgeppuOcnk+ppaKBhRnaQw7daKKAJRyM0jruXHftTYzg7fyqSmIgB4pwGKJBht3Y9aQUgFooooAQ9frUYPlyf7JqU8imMu5fegCQmkJpKcB60AIBmlApaKAGEbTjselRuSrhhUxGRTCNwINAEgORkUVFC3VD26VLQAwjB9jTJQCufSpSMjFMI3KQetAD1IIBByKCcVHFkLg+tOoAUnNJRRQMRhkEUAYGKWigBIjyQfrUlRH5ZAak3CgQtNbg59aNxpufU0AORtpIPQ1IWAGSaiyDSYAPSgBRySfU0Ud8UUDId5EhYVYBBGR0qCVf4h+NLC+DtPQ9KBE9FFITigBaQmkJpKAA8nNFFFAwpCMjBpaUCgBiLtB9acOTjPNOK8VXLEPnuDQIuU1xlSKdRTAhHNHbFHTI9KKQBRRR14AzQAUdeBzThH\/eP4CngADAFADBGf4j+Ap4AAwBS0UwCims4HufSmFmPsPagB7OB9fSmFmPsKTpRSAKKVec+1IwIye1ABQRkYNFFAxUOVweo4p1MHWlQ8EelAh1MIw315pxOBSHmgBKKKKBhRRgkcUrjGD+FAh0R+XHcU+oQdrg9jwampgQOCpIH1FPBDAEd6WUfLu9KZGcEr+IpAPooooAKawwc\/hTqQ4IwaAI2O0hvTr9KlpnUYNCE7cHqOKAH01uenWkzQMt90Z96ACgZP3RmnCMfxHP8qfRYBgj\/ALxz7U8DHSlprOF9z6CmA6ms4XqefSmFmb2FIABSuApdj0+UfrSAYoooGFFFFABRRRQAUUUUAIfUdR0qZW3KCKipUO1sdj\/OgRIQCCD3qEZBweoqeo5V43Dt1+lMBKKQE4opAGfSiijk0AMkJUqw7GpVIYAjvTCMgg02JtrFG\/CgCaiiigAprDHP506igCCQFWDiplbcoI70wjqppsWV3KaAJqYeuRRmigAooooGFFFFABRRRQAhGcUtFFAB2p8YAUHuabg4pYjxt9On0oENlYK6\/rTxyKSVNy5HUVHC38J\/CgCWm7efanUUAMI5INQMCrYqywyPeonG5c9xQA+N96+460EYOO38qjhHU\/hUlABRRQeBmgYUE4GaKRhlSKACJ85B61JVVSVYH0qxvHHvQIdUM6\/xD8ampCARg9KAP\/\/Z";
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            // headers to tell that result is HTML
            header('Content-type: text/html');
            $html = "<img src='data:image/jpeg;base64, $byte_array'/>";
            echo $html;
            exit;
        }
        protected function getDateDiff($date1,$date2)
        {
            $diff = $date1 - $date2;
                
            define('MINUTE',60,true);
            define('HOUR',60*60,true);
            define('DAY',60*60*24, true);
            define('MONTH',DAY*30, true);
            define('YEAR',DAY*365, true);
                
            $y = floor($diff / (YEAR));
            $mon = floor(($diff - $y * YEAR) / (MONTH));
            $d = floor(($diff - $y * YEAR - $mon*MONTH ) / (DAY));
            $h = floor(($diff - $y * YEAR - $mon*MONTH - $d*DAY) / (HOUR));
            $m = floor(($diff - $y * YEAR - $mon*MONTH - $d*DAY - $h*HOUR) / (MINUTE));
            
            $span="";
                
            if($y>0)
            {
                if($y>1)
                    $span .= $y ." years, ";
                else
                    $span .= $y ." year, ";
            }
            if($mon>0)
            {
                if($mon>1)
                    $span .= $mon ." months, ";
                else
                    $span .= $mon ." month, ";
            }
            if($d>0)
            {
                if($d>1)
                    $span .= $d ." days, ";
                else
                    $span .= $d ." day, ";
            }
            if($h>0 & $mon==0 & $y==0 & $d==0)
            {
                if($h>1)
                    $span .= $h ." hours, ";
                else
                    $span .= $h ." hour, ";
            }
            if($m>0 & $mon==0 & $y==0 & $d==0)
            {
                if($d>1)
                    $span .= $m ." minutes";
                else
                    $span .= $m ." minute";
            }
            $span = rtrim($span,", ");
            return $span;
        }
    }

?>